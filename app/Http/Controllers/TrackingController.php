<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Display tracking details for a specific order.
     */
    public function show(string $id): View
    {
        $shipment = Shipment::where('tracking_code', $id)
            ->orWhere('id', $id)
            ->with('request.product')
            ->first();

        return view('tracking', [
            'id' => $id,
            'shipment' => $shipment,
        ]);
    }
}
