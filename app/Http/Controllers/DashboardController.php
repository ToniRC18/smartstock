<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Product;
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

        $contracts = ClientContract::with('product')
            ->where('client_id', $clientId)
            ->get();

        $summary = [
            'in_use' => $contracts->sum('card_current_amount'),
            'inactive' => $contracts->sum('card_inactive_amount'),
            'limit' => $contracts->sum('card_limit_amount'),
            'available' => $contracts->sum(fn (ClientContract $contract) => $contract->availableByLimit()),
        ];

        return view('dashboard', [
            'contracts' => $contracts,
            'summary' => $summary,
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

        return view('dashboard-admin', [
            'clients' => $clients,
            'criticalProducts' => $criticalProducts,
            'worstContracts' => $worstContracts,
        ]);
    }
}
