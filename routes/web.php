<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Web routes for SmartStock landing, dashboards, and tracking views.
Route::get('/', [LandingController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/admin/dashboard', [DashboardController::class, 'admin']);
Route::get('/tracking/{id}', [TrackingController::class, 'show']);
