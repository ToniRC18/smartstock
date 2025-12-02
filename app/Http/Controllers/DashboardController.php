<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Product;
use App\Models\CardRequest;
use App\Models\Shipment;
use App\Models\ContractAllocation;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Show the standard dashboard for company users with contract data.
     */
    public function index(Request $request): View
    {
        // Hardcoded client context for now; replace with auth/tenancy logic later.
        $clientId = (int) $request->input('client_id', 1);

        $contracts = ClientContract::with(['product', 'allocations.product'])
            ->where('client_id', $clientId)
            ->get();
        $allClients = Client::orderBy('id')->get();

        $summary = [
            'in_use' => $contracts->sum('card_current_amount'),
            'inactive' => $contracts->sum('card_inactive_amount'),
            'limit' => $contracts->sum('card_limit_amount'),
            'expired' => $contracts->sum('card_expired_amount'),
            'available' => $contracts->sum(fn (ClientContract $contract) => $contract->availableByLimit()),
            'available_for_new' => $contracts->sum(fn (ClientContract $contract) => $contract->availableForNewAssignments()),
        ];

        $products = Product::all();

        $requests = CardRequest::with(['product', 'shipment'])
            ->where('client_id', $clientId)
            ->latest()
            ->take(5)
            ->get();

        $shipments = Shipment::with(['request.product'])
            ->whereHas('request', fn ($q) => $q->where('client_id', $clientId))
            ->latest()
            ->get();
        $orders = Order::with('product')
            ->where('client_id', $clientId)
            ->latest()
            ->take(5)
            ->get();

        $allocationAvailability = $contracts->mapWithKeys(function (ClientContract $contract) {
            $allocations = ContractAllocation::where('client_contract_id', $contract->id)->get()->mapWithKeys(function ($alloc) {
                $available = max(0, (int) $alloc->card_limit_amount - (int) $alloc->card_current_amount - (int) $alloc->card_expired_amount);
                if ($alloc->card_inactive_amount > 0) {
                    $available = min($available, (int) $alloc->card_current_amount);
                }
                return [$alloc->product_id => $available];
            });
            return [$contract->id => $allocations];
        });

        return view('dashboard', [
            'contracts' => $contracts,
            'summary' => $summary,
            'products' => $products,
            'clientId' => $clientId,
            'requests' => $requests,
            'shipments' => $shipments,
            'orders' => $orders,
            'contractAvailability' => $contracts->mapWithKeys(function (ClientContract $contract) {
                $available = $contract->availableForNewAssignments();
                if ($contract->card_inactive_amount > 0) {
                    $available = min($available, (int) $contract->card_current_amount);
                }
                return [$contract->id => $available];
            }),
            'allocationAvailability' => $allocationAvailability,
            'allClients' => $allClients,
        ]);
    }

    /**
     * Show the admin dashboard variant with stock and contract health data.
     */
    public function admin(): View
    {
        $clients = Client::with(['contracts.product'])->get();

        $criticalProducts = Product::all()->filter->isStockCritical()->values();

        $worstContracts = ClientContract::with(['client', 'product'])
            ->orderByDesc('card_inactive_amount')
            ->take(5)
            ->get();

        $requestsByClient = CardRequest::with(['client', 'product', 'contract', 'shipment'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('client_id');

        $allRequests = $requestsByClient->flatten(1)->map(function (CardRequest $req) {
            $contract = $req->contract;
            return [
                'id' => $req->id,
                'client_id' => $req->client_id,
                'client_name' => $req->client?->name,
                'product' => $req->product->name ?? 'Producto',
                'reason' => $req->reason,
                'quantity' => $req->quantity,
                'status' => $req->status,
                'contract' => $contract?->id,
                'contract_info' => $contract ? [
                    'limit' => $contract->card_limit_amount,
                    'current' => $contract->card_current_amount,
                    'inactive' => $contract->card_inactive_amount,
                    'expired' => $contract->card_expired_amount,
                    'available' => $contract->availableForNewAssignments(),
                    'available_new' => (function () use ($contract) {
                        $available = $contract->availableForNewAssignments();
                        if ($contract->card_inactive_amount > 0) {
                            $available = min($available, (int) $contract->card_current_amount);
                        }
                        return $available;
                    })(),
                ] : null,
                'tracking' => $req->shipment?->tracking_code,
                'admin_note' => $req->admin_note,
                'notes' => $req->notes,
            ];
        })->values();

        $requestsByClientArray = $requestsByClient->map(function ($group) {
            return $group->map(function (CardRequest $req) {
                $contract = $req->contract;
                return [
                    'id' => $req->id,
                    'client_id' => $req->client_id,
                    'client_name' => $req->client?->name,
                    'product' => $req->product->name ?? 'Producto',
                    'reason' => $req->reason,
                    'quantity' => $req->quantity,
                    'status' => $req->status,
                    'contract' => $contract?->id,
                    'contract_info' => $contract ? [
                        'limit' => $contract->card_limit_amount,
                        'current' => $contract->card_current_amount,
                        'inactive' => $contract->card_inactive_amount,
                        'expired' => $contract->card_expired_amount,
                        'available' => $contract->availableForNewAssignments(),
                        'available_new' => (function () use ($contract) {
                            $available = $contract->availableForNewAssignments();
                            if ($contract->card_inactive_amount > 0) {
                                $available = min($available, (int) $contract->card_current_amount);
                            }
                            return $available;
                        })(),
                    ] : null,
                    'tracking' => $req->shipment?->tracking_code,
                    'admin_note' => $req->admin_note,
                    'notes' => $req->notes,
                ];
            })->toArray();
        })->toArray();

        $products = Product::all();

        $shipments = Shipment::with(['request.client', 'request.product'])
            ->orderByDesc('created_at')
            ->get();

        $pendingByProduct = CardRequest::where('status', 'pending')
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_pending')
            ->groupBy('product_id')
            ->pluck('total_pending', 'product_id');

        return view('dashboard-admin', [
            'clients' => $clients,
            'criticalProducts' => $criticalProducts,
            'worstContracts' => $worstContracts,
            'requestsByClient' => $requestsByClientArray,
            'allRequests' => $allRequests,
            'products' => $products,
            'shipments' => $shipments,
            'pendingByProduct' => $pendingByProduct,
        ]);
    }

    /**
     * Crear cliente con contratos base (Combustible, Despensa, Premios).
     */
    public function storeClient(Request $request): RedirectResponse
    {
        $products = Product::whereIn('name', ['Combustible', 'Despensa', 'Premios'])->get()->keyBy('name');

        if ($request->boolean('existing_client')) {
            $validated = $request->validate([
                'client_id' => ['required', 'exists:clients,id'],
                'contract_name' => ['required', 'string', 'max:255'],
                'limits' => ['required', 'array'],
                'limits.*' => ['nullable', 'integer', 'min:0'],
            ]);

            $clientId = (int) $validated['client_id'];
            $contractLimit = collect($validated['limits'])->sum();

            $contract = ClientContract::create([
                'client_id' => $clientId,
                'product_id' => $products->first()->id,
                'name' => $validated['contract_name'],
                'card_limit_amount' => $contractLimit,
                'card_current_amount' => 0,
                'card_inactive_amount' => 0,
                'card_expired_amount' => 0,
            ]);

            foreach ($products as $product) {
                $limit = (int) ($validated['limits'][$product->name] ?? 0);
                ContractAllocation::create([
                    'client_contract_id' => $contract->id,
                    'product_id' => $product->id,
                    'card_limit_amount' => $limit,
                    'card_current_amount' => 0,
                    'card_inactive_amount' => 0,
                    'card_expired_amount' => 0,
                ]);
            }

            return back()->with('status', 'Contrato agregado al cliente.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contract_name' => ['required', 'string', 'max:255'],
            'limits' => ['required', 'array'],
            'limits.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $client = Client::create([
            'name' => $validated['name'],
        ]);

        $contractLimit = collect($validated['limits'])->sum();

        $contract = ClientContract::create([
            'client_id' => $client->id,
            'product_id' => $products->first()->id,
            'name' => $validated['contract_name'],
            'card_limit_amount' => $contractLimit,
            'card_current_amount' => 0,
            'card_inactive_amount' => 0,
            'card_expired_amount' => 0,
        ]);

        foreach ($products as $product) {
            $limit = (int) ($validated['limits'][$product->name] ?? 0);
            ContractAllocation::create([
                'client_contract_id' => $contract->id,
                'product_id' => $product->id,
                'card_limit_amount' => $limit,
                'card_current_amount' => 0,
                'card_inactive_amount' => 0,
                'card_expired_amount' => 0,
            ]);
        }

        return back()->with('status', 'Empresa creada con contratos base.');
    }

    public function updateProductStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock_current' => ['required', 'integer', 'min:0'],
            'stock_minimum' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return back()->with('status', 'Inventario actualizado.');
    }
}
