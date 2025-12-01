<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Product;
use App\Models\CardRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        $summary = [
            'in_use' => $contracts->sum('card_current_amount'),
            'inactive' => $contracts->sum('card_inactive_amount'),
            'limit' => $contracts->sum('card_limit_amount'),
            'expired' => $contracts->sum('card_expired_amount'),
            'available' => $contracts->sum(fn (ClientContract $contract) => $contract->availableByLimit()),
            'available_for_new' => $contracts->sum(fn (ClientContract $contract) => $contract->availableForNewAssignments()),
        ];

        $products = Product::all();

        $requests = CardRequest::with('product')
            ->where('client_id', $clientId)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'contracts' => $contracts,
            'summary' => $summary,
            'products' => $products,
            'clientId' => $clientId,
            'requests' => $requests,
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

        $requestsByClient = CardRequest::with(['client', 'product', 'contract'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('client_id');

        $requestsByClientArray = $requestsByClient->map(function ($group) {
            return $group->map(function (CardRequest $req) {
                $contract = $req->contract;
                return [
                    'id' => $req->id,
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
                    ] : null,
                    'admin_note' => $req->admin_note,
                    'notes' => $req->notes,
                ];
            })->toArray();
        })->toArray();

        return view('dashboard-admin', [
            'clients' => $clients,
            'criticalProducts' => $criticalProducts,
            'worstContracts' => $worstContracts,
            'requestsByClient' => $requestsByClientArray,
        ]);
    }
}
