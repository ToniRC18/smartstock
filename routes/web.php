<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\CardRequestController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Web routes for SmartStock landing, dashboards, and tracking views.
Route::get('/', [LandingController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/admin/dashboard', [DashboardController::class, 'admin']);
Route::post('/dashboard/requests', [CardRequestController::class, 'store'])->name('dashboard.requests.store');
Route::patch('/admin/requests/{cardRequest}/status', [CardRequestController::class, 'updateStatus'])->name('admin.requests.update');
Route::patch('/admin/shipments/{shipment}', [CardRequestController::class, 'updateShipment'])->name('admin.shipments.update');
Route::post('/admin/clients', [DashboardController::class, 'storeClient'])->name('admin.clients.store');
Route::patch('/admin/products/{product}', [DashboardController::class, 'updateProductStock'])->name('admin.products.update');
Route::post('/simular-pedido', [OrderController::class, 'simular'])->name('orders.simular');
Route::post('/crear-pedido', [OrderController::class, 'crearPedido'])->name('orders.crear');
Route::post('/admin/pedido/{id}/estado', [OrderController::class, 'actualizarEstado'])->name('orders.estado');
Route::get('/tracking/{codigo}', [OrderController::class, 'tracking'])->name('orders.tracking');
