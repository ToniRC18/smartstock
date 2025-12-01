<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    public function update(Shipment $shipment, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_code' => ['required', 'string', 'max:50', Rule::unique('shipments')->ignore($shipment->id)],
            'status' => ['required', 'in:pendiente_envio,preparacion,en_ruta,entregado'],
            'eta_date' => ['nullable', 'date'],
        ]);

        $shipment->update($validated);

        return back()->with('status', 'Envío actualizado.');
    }
}
