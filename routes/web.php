<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\CardRequestController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Web routes for SmartStock landing, dashboards, and tracking views.
Route::get('/', [LandingController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/admin/dashboard', [DashboardController::class, 'admin']);
Route::get('/tracking/{id}', [TrackingController::class, 'show']);
Route::post('/dashboard/requests', [CardRequestController::class, 'store'])->name('dashboard.requests.store');
Route::patch('/admin/requests/{cardRequest}/status', [CardRequestController::class, 'updateStatus'])->name('admin.requests.update');
Route::patch('/admin/shipments/{shipment}', [CardRequestController::class, 'updateShipment'])->name('admin.shipments.update');
Route::post('/admin/clients', [DashboardController::class, 'storeClient'])->name('admin.clients.store');
