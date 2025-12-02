<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CardRequest;
use App\Models\ClientContract;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ContractAllocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CardRequestController extends Controller
{
    /**
     * Store a new card request (reposicion o nuevas).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'contract_id' => ['required', 'exists:client_contracts,id'],
            'product_id' => ['required', 'exists:products,id'],
            'reason' => ['required', 'in:expired,lost,new_employee'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $contract = ClientContract::with('product')->findOrFail($validated['contract_id']);
        if ($contract->client_id !== (int) $validated['client_id']) {
            return back()->withErrors(['contract_id' => 'El contrato no pertenece a este cliente'])->withInput();
        }

        $product = Product::findOrFail($validated['product_id']);
        if ($product->id !== $contract->product_id) {
            return back()->withErrors(['product_id' => 'El producto debe coincidir con el contrato seleccionado.'])->withInput();
        }
        $quantity = (int) $validated['quantity'];
        $reason = $validated['reason'];

        // Disponibilidad por producto (allocation)
        $allocation = ContractAllocation::where('client_contract_id', $contract->id)
            ->where('product_id', $product->id)
            ->first();

        $availableByProduct = null;
        if ($allocation) {
            $availableByProduct = max(0, (int) $allocation->card_limit_amount - (int) $allocation->card_current_amount - (int) $allocation->card_expired_amount);
            if ($allocation->card_inactive_amount > 0) {
                $availableByProduct = min($availableByProduct, (int) $allocation->card_current_amount);
            }
            if ($quantity > $availableByProduct) {
                return back()->withErrors(['quantity' => 'Excede el disponible por producto en este contrato.'])->withInput();
            }
        }

        // Validar contra el límite total del contrato.
        if ($quantity > $contract->card_limit_amount) {
            return back()->withErrors(['quantity' => 'Excede el límite total del contrato para este producto.'])->withInput();
        }

        // Validaciones según motivo
        if ($reason === 'new_employee') {
            $availableByLimit = $contract->availableForNewAssignments();
            if ($contract->card_inactive_amount > 0) {
                $availableByLimit = min($availableByLimit, (int) $contract->card_current_amount);
            }
            if ($quantity > $availableByLimit) {
                return back()->withErrors(['quantity' => 'Excede el disponible para nuevas asignaciones (considerando tarjetas inactivas).'])->withInput();
            }
        } elseif ($reason === 'expired') {
            // Reposición, no limita por disponible
        } elseif ($reason === 'lost') {
            // Reposición por pérdida: no aplica validación de disponible, pero respeta stock.
        } else {
            $availableByLimit = $contract->availableForNewAssignments();
            if ($quantity > $availableByLimit) {
                return back()->withErrors(['quantity' => 'Cantidad solicitada excede el disponible del contrato.'])->withInput();
            }
        }

        if ($product->stock_current < $quantity) {
            return back()->withErrors(['quantity' => 'No hay stock suficiente para esta solicitud.'])->withInput();
        }

        CardRequest::create($validated + ['status' => 'pending']);

        return back()->with('status', 'Solicitud registrada, en revisión.');
    }

    /**
     * Update status (approve/reject) for admin flow.
     */
    public function updateStatus(CardRequest $cardRequest, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['status'] === 'approved') {
            $product = $cardRequest->product;
            if ($product && $product->stock_current < (int) $cardRequest->quantity) {
                return back()->withErrors(['status' => 'Stock insuficiente para aprobar esta solicitud.']);
            }
            $contract = $cardRequest->contract;
            if ($contract && (int) $cardRequest->quantity > $contract->card_limit_amount) {
                return back()->withErrors(['status' => 'Excede el límite total del contrato para este producto.']);
            }
            // Validar disponible por producto al aprobar
            $allocation = ContractAllocation::where('client_contract_id', $contract->id)
                ->where('product_id', $product->id)
                ->first();
            if ($allocation) {
                $availableByProduct = max(0, (int) $allocation->card_limit_amount - (int) $allocation->card_current_amount - (int) $allocation->card_expired_amount);
                if ($allocation->card_inactive_amount > 0) {
                    $availableByProduct = min($availableByProduct, (int) $allocation->card_current_amount);
                }
                if ((int) $cardRequest->quantity > $availableByProduct) {
                    return back()->withErrors(['status' => 'Excede el disponible por producto en este contrato.']);
                }
            }
            if ($cardRequest->reason === 'new_employee') {
                $contract = $cardRequest->contract;
                if ($contract) {
                    $available = $contract->availableForNewAssignments();
                    if ($contract->card_inactive_amount > 0) {
                        $available = min($available, (int) $contract->card_current_amount);
                    }
                    if ((int) $cardRequest->quantity > $available) {
                        return back()->withErrors(['status' => 'No es aplicable: excede el disponible para nuevas asignaciones.']);
                    }
                }
            }
        }

        $cardRequest->update($validated);

        if ($validated['status'] === 'approved') {
            $this->approveRequest($cardRequest->fresh());
        }

        return back()->with('status', 'Solicitud actualizada.');
    }

    /**
     * Update shipment info (tracking, status, ETA).
     */
    public function updateShipment(Shipment $shipment, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_code' => ['required', 'string', 'max:50', Rule::unique('shipments')->ignore($shipment->id)],
            'status' => ['required', 'in:pendiente_envio,preparacion,en_ruta,entregado'],
            'eta_date' => ['nullable', 'date'],
        ]);

        $shipment->update($validated);

        return back()->with('status', 'Envío actualizado.');
    }

    /**
     * Apply business effects when a request is approved.
     */
    private function approveRequest(CardRequest $cardRequest): void
    {
        $contract = $cardRequest->contract()->first();
        $product = $cardRequest->product()->first();

        if (!$contract || !$product) {
            return;
        }

        $qty = (int) $cardRequest->quantity;

        if ($product->stock_current < $qty) {
            // Leave request approved but do not mutate stock/contract to avoid inconsistency.
            return;
        }

        // Ajustes por motivo.
        switch ($cardRequest->reason) {
            case 'new_employee':
                $contract->card_current_amount = min(
                    $contract->card_limit_amount,
                    $contract->card_current_amount + $qty
                );
                break;
            case 'expired':
                $contract->card_expired_amount = max(0, $contract->card_expired_amount - $qty);
                break;
            case 'lost':
                // No se altera el conteo; es reposición.
                break;
        }

        $product->stock_current = max(0, $product->stock_current - $qty);

        $contract->save();
        $product->save();

        // Generar envío si no existe.
        Shipment::firstOrCreate(
            ['card_request_id' => $cardRequest->id],
            [
                'tracking_code' => 'TEMP-' . strtoupper(bin2hex(random_bytes(3))),
                'status' => 'pendiente_envio',
                'eta_date' => now()->addDays(3)->toDateString(),
            ]
        );
    }
}
