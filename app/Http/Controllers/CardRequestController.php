<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CardRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $cardRequest->update($validated);

        return back()->with('status', 'Solicitud actualizada.');
    }
}
