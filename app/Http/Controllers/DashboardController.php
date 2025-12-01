<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Show the standard dashboard for company users.
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Show the admin dashboard variant.
     */
    public function admin()
    {
        return view('dashboard-admin');
    }
}
