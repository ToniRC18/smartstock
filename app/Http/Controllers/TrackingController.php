<?php

namespace App\Http\Controllers;

class TrackingController extends Controller
{
    /**
     * Display tracking details for a specific order.
     */
    public function show(string $id)
    {
        return view('tracking', ['id' => $id]);
    }
}
