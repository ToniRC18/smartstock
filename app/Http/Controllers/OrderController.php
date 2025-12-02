<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ClientContract;
use App\Models\ContractAllocation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function simular(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $check = $this->runValidation((int) $data['client_id'], (int) $data['product_id'], (int) $data['cantidad']);

        return response()->json($check);
    }

    public function crearPedido(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $check = $this->runValidation((int) $data['client_id'], (int) $data['product_id'], (int) $data['cantidad']);

        if ($check['estado'] === 'rechazado') {
            return back()->withErrors(['pedido' => $check['motivo']])->withInput();
        }

        $product = Product::find($data['product_id']);
        $contract = $this->getContractForProduct((int) $data['client_id'], (int) $data['product_id']);
        $allocation = $contract ? ContractAllocation::where('client_contract_id', $contract->id)->where('product_id', $product->id)->first() : null;

        $aprobada = min($data['cantidad'], $check['max_permitido']);
        if ($aprobada <= 0) {
            return back()->withErrors(['pedido' => 'No hay disponible para aprobar.'])->withInput();
        }

        // Crear Order
        $order = Order::create([
            'client_id' => $data['client_id'],
            'product_id' => $data['product_id'],
            'cantidad_solicitada' => $data['cantidad'],
            'cantidad_aprobada' => $aprobada,
            'estado' => 'pendiente',
            'motivo_rechazo' => $check['estado'] === 'advertencia' ? $check['motivo'] : null,
            'codigo_tracking' => 'ORD-' . Str::upper(Str::random(8)),
            'fecha_estimada' => now()->addDays(2)->toDateString(),
        ]);

        // Descontar stock
        if ($product) {
            $product->decrement('stock_current', $aprobada);
        }

        // Actualizar contrato/allocation
        if ($contract) {
            $contract->increment('card_current_amount', $aprobada);
        }
        if ($allocation) {
            $allocation->increment('card_current_amount', $aprobada);
        }

        return back()->with('status', 'Pedido creado. Tracking: ' . $order->codigo_tracking);
    }

    public function actualizarEstado(int $id, Request $request): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,rechazado,preparando,en_ruta,entregado'],
            'motivo_rechazo' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedNext = [
            'pendiente' => ['preparando', 'rechazado'],
            'preparando' => ['en_ruta', 'rechazado'],
            'en_ruta' => ['entregado'],
            'rechazado' => [],
            'entregado' => [],
        ];

        $current = $order->estado;
        if (!in_array($data['estado'], $allowedNext[$current] ?? [], true) && $data['estado'] !== $current) {
            return back()->withErrors(['estado' => 'Transición no permitida.']);
        }

        $order->update([
            'estado' => $data['estado'],
            'motivo_rechazo' => $data['motivo_rechazo'] ?? $order->motivo_rechazo,
        ]);

        return back()->with('status', 'Estado de pedido actualizado.');
    }

    public function tracking(string $codigo): View
    {
        $order = Order::where('codigo_tracking', $codigo)
            ->orWhere('id', $codigo)
            ->with('product')
            ->first();

        $shipment = Shipment::where('tracking_code', $codigo)
            ->orWhere('id', $codigo)
            ->with('request.product')
            ->first();

        return view('tracking', [
            'order' => $order,
            'shipment' => $shipment,
            'id' => $codigo,
        ]);
    }

    /**
     * Ejecuta las validaciones de negocio para solicitudes/pedidos.
     */
    private function runValidation(int $clientId, int $productId, int $cantidad): array
    {
        $contract = $this->getContractForProduct($clientId, $productId);
        $allocation = $contract ? ContractAllocation::where('client_contract_id', $contract->id)->where('product_id', $productId)->first() : null;
        $product = Product::find($productId);

        $available = $allocation
            ? max(0, (int) $allocation->card_limit_amount - (int) $allocation->card_current_amount - (int) $allocation->card_expired_amount)
            : 0;
        $inactive = $allocation ? (int) $allocation->card_inactive_amount : 0;
        $current = $allocation ? (int) $allocation->card_current_amount : 0;

        if ($inactive > 0) {
            $available = min($available, $current);
        }

        if ($cantidad > $available) {
            return [
                'estado' => 'rechazado',
                'max_permitido' => $available,
                'motivo' => 'Excede el límite disponible por producto en el contrato.',
            ];
        }

        if ($product && $product->stock_current < $cantidad) {
            return [
                'estado' => 'rechazado',
                'max_permitido' => $product->stock_current,
                'motivo' => 'Stock insuficiente en almacén.',
            ];
        }

        $estado = 'aprobado';
        $motivo = 'OK';
        $ratio = ($current + $inactive) > 0 ? $inactive / ($current + $inactive) : 0;
        if ($ratio >= 0.5) {
            $estado = 'advertencia';
            $motivo = 'Muchas tarjetas inactivas, se sugiere depurar antes de pedir más.';
        }

        return [
            'estado' => $estado,
            'max_permitido' => $available,
            'motivo' => $motivo,
        ];
    }

    private function getContractForProduct(int $clientId, int $productId): ?ClientContract
    {
        return ClientContract::where('client_id', $clientId)
            ->whereHas('allocations', fn ($q) => $q->where('product_id', $productId))
            ->with(['allocations' => fn ($q) => $q->where('product_id', $productId)])
            ->first()
            ?? ClientContract::where('client_id', $clientId)->where('product_id', $productId)->first();
    }
}
