<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    /**
     * Display the landing page template.
     */
    public function index()
    {
        return view('landing');
    }
}
