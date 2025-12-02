{{-- Dashboard admin enfocado en listar empresas y ver detalles de contratos al seleccionar --}}
@extends('layouts.app')

@section('content')
@php
    $clientData = ($clients ?? collect())->map(function ($client) {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'summary' => [
                'contracts' => $client->contracts->count(),
                'limit' => $client->contracts->sum('card_limit_amount'),
                'in_use' => $client->contracts->sum('card_current_amount'),
                'inactive' => $client->contracts->sum('card_inactive_amount'),
                'available' => $client->contracts->sum(fn ($c) => $c->availableByLimit()),
            ],
            'contracts' => $client->contracts->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'product' => optional($contract->product)->name ?? 'Producto',
                    'limit' => $contract->card_limit_amount,
                    'current' => $contract->card_current_amount,
                    'inactive' => $contract->card_inactive_amount,
                    'expired' => $contract->card_expired_amount,
                    'available' => $contract->availableByLimit(),
                    'available_new' => (function () use ($contract) {
                        $available = $contract->availableForNewAssignments();
                        if ($contract->card_inactive_amount > 0) {
                            $available = min($available, (int) $contract->card_current_amount);
                        }
                        return $available;
                    })(),
                    'allocations' => $contract->allocations->map(function ($alloc) {
                        return [
                            'product' => optional($alloc->product)->name ?? 'Producto',
                            'limit' => $alloc->card_limit_amount,
                            'current' => $alloc->card_current_amount,
                            'inactive' => $alloc->card_inactive_amount,
                            'expired' => $alloc->card_expired_amount,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    })->values();
@endphp
<div class="min-h-screen flex bg-slate-100">
    <aside class="w-64 bg-ss-dark text-white flex flex-col border-r border-slate-800">
        <div class="px-6 py-6 border-b border-slate-800">
            <a href="/" class="text-lg font-semibold text-white">SmartStock</a>
            <p class="text-sm text-slate-300">Panel admin</p>
        </div>
        @php
            $allRequestsCollection = collect($requestsByClient ?? [])->flatten(1);
            $pendingRequestsCount = $allRequestsCollection->where('status', 'pending')->count();
            $shipmentsCount = ($shipments ?? collect())->count();
            $criticalCount = ($products ?? collect())->filter->isStockCritical()->count();
        @endphp
        <nav class="flex-1 px-4 py-6 space-y-3 text-sm">
            <button data-view="empresas" class="w-full text-left px-3 py-2 rounded-lg bg-white/10 text-white font-medium flex items-center justify-between">
                <span>Empresas</span>
                <span class="text-xs px-2 py-1 rounded-full bg-white/15 text-white">{{ $clientData->count() }}</span>
            </button>
            <button data-view="solicitudes" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5 flex items-center justify-between">
                <span>Solicitudes</span>
                <span class="text-xs px-2 py-1 rounded-full bg-white/10 text-white">{{ $pendingRequestsCount }}</span>
            </button>
            <button data-view="envios" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5 flex items-center justify-between">
                <span>Envíos</span>
                <span class="text-xs px-2 py-1 rounded-full bg-white/10 text-white">{{ $shipmentsCount }}</span>
            </button>
            <button data-view="inventario" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5 flex items-center justify-between">
                <span>Inventario</span>
                <span class="text-xs px-2 py-1 rounded-full bg-white/10 text-white">{{ $criticalCount }}</span>
            </button>
        </nav>
        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            Supervisión central
        </div>
    </aside>

    <main class="flex-1 p-8 space-y-8">
        @if (session('status'))
            <div id="flash-success" class="rounded-lg border border-ss-emerald/30 bg-ss-emerald/10 text-ss-emerald px-4 py-3 text-sm shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <header class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Empresas en contrato</p>
                    <h1 class="text-2xl font-semibold text-slate-900">Dashboard Admin</h1>
                </div>
                <span class="px-4 py-2 rounded-lg bg-ss-emerald/10 text-ss-emerald text-sm font-semibold">
                    {{ $clientData->count() }} empresas
                </span>
            </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500">Solicitudes pendientes</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ $pendingRequestsCount }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Revisar</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Envíos activos</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $shipmentsCount }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Seguimiento</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Stock crítico</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $criticalCount }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-ss-emerald/10 text-ss-emerald">Inventario</span>
                </div>
            </div>
        </header>

        <section data-section="empresas">
            @if ($clientData->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-10 text-center text-slate-600">
                    Aún no hay clientes cargados. Ejecuta los seeders o agrega contratos para ver la lista.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="client-panels">
                    <section id="client-list" class="col-span-3 lg:col-span-3 bg-white border border-slate-200 rounded-xl shadow-sm p-6 max-w-5xl w-full mx-auto">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-slate-500">Empresas</p>
                                <h2 class="text-lg font-semibold text-slate-900">Selecciona para ver detalles</h2>
                            </div>
                            <button id="open-client-modal" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm">Agregar empresa</button>
                        </div>
                        <div class="space-y-3">
                            @foreach ($clientData as $client)
                                <button
                                    type="button"
                                    data-client-id="{{ $client['id'] }}"
                                    onclick="selectClient({{ $client['id'] }})"
                                    class="w-full text-left rounded-lg border border-slate-200 px-4 py-3 bg-slate-50 hover:bg-ss-emerald/10 hover:border-ss-emerald transition">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-slate-900">{{ $client['name'] }}</p>
                                        <span class="text-xs font-semibold text-ss-emerald bg-ss-emerald/10 px-2 py-1 rounded-full">
                                            {{ $client['summary']['contracts'] }} contratos
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-600 flex flex-wrap gap-3">
                                        <span>En uso: {{ $client['summary']['in_use'] }}</span>
                                        <span>Disponible: {{ $client['summary']['available'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    <section id="client-detail" class="lg:col-span-2 space-y-6 hidden">
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                                <div class="flex items-center gap-3">
                                    <button id="back-to-list" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs">← Regresar</button>
                                    <div>
                                        <p class="text-sm text-slate-500">Detalle de cliente</p>
                                        <h2 id="detail-name" class="text-lg font-semibold text-slate-900">Seleccione una empresa</h2>
                                    </div>
                                </div>
                                <span id="detail-contract-count" class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">- contratos</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6 py-5" id="summary-cards">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-500">Tarjetas en uso</p>
                                    <p id="detail-in-use" class="text-2xl font-semibold text-slate-900 mt-1">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-500">Tarjetas sin uso</p>
                                    <p id="detail-inactive" class="text-2xl font-semibold text-slate-900 mt-1">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-500">Límite de contrato</p>
                                    <p id="detail-limit" class="text-2xl font-semibold text-slate-900 mt-1">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-500">Disponible para solicitar</p>
                                    <p id="detail-available" class="text-2xl font-semibold text-slate-900 mt-1">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                                <div>
                                    <p class="text-sm text-slate-500">Contratos del cliente</p>
                                    <h3 class="text-lg font-semibold text-slate-900">Detalle por contrato y producto</h3>
                                </div>
                                <button class="text-sm text-ss-emerald font-semibold" data-open-contract-modal data-client-id="">+ Nuevo contrato</button>
                            </div>
                            <div id="contracts-accordion" class="divide-y divide-slate-100">
                                <div class="px-6 py-4 text-center text-slate-500">Selecciona una empresa para ver sus contratos.</div>
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </section>

        <section data-section="solicitudes" class="hidden space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 flex-wrap gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Solicitudes</p>
                        <h3 class="text-lg font-semibold text-slate-900">Todas las empresas</h3>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div>
                            <label class="text-xs text-slate-500">Estado</label>
                            <select id="filter-status" class="ml-2 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                <option value="all">Todos</option>
                                <option value="pending">Pendiente</option>
                                <option value="approved">Aprobada</option>
                                <option value="rejected">Rechazada</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Empresa</label>
                            <select id="filter-client" class="ml-2 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                <option value="all">Todas</option>
                                @foreach ($clientData as $client)
                                    <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div id="all-requests-container" class="divide-y divide-slate-100">
                    <div class="px-6 py-4 text-center text-slate-500">No hay solicitudes registradas.</div>
                </div>
            </div>
        </section>

        <section data-section="envios" class="hidden space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Envíos</p>
                        <h3 class="text-lg font-semibold text-slate-900">Pendientes y activos</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Empresa</th>
                                <th class="px-6 py-3">Tracking</th>
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">ETA</th>
                                <th class="px-6 py-3">Código / Paquetería</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($shipments ?? []) as $shipment)
                                <tr class="border-t border-slate-100">
                                    <td class="px-6 py-3">{{ $shipment->request->client->name ?? 'Empresa' }}</td>
                                    <td class="px-6 py-3 font-semibold text-ss-emerald">#{{ $shipment->tracking_code }}</td>
                                    <td class="px-6 py-3">{{ $shipment->request->product->name ?? 'Producto' }}</td>
                                    <td class="px-6 py-3 capitalize">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ str_replace('_', ' ', $shipment->status) }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600">{{ $shipment->eta_date ?? 'Pendiente' }}</td>
                                    <td class="px-6 py-3">
                                        <form action="{{ route('admin.shipments.update', $shipment) }}" method="POST" class="flex flex-wrap gap-2 items-center">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex flex-col gap-1">
                                                <input name="tracking_code" value="{{ $shipment->tracking_code }}" class="rounded-lg border border-slate-200 px-2 py-1 text-xs" />
                                                <select name="carrier" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                                    <option value="">Selecciona paquetería</option>
                                                    <option value="estafeta" @selected($shipment->carrier === 'estafeta')>Estafeta</option>
                                                    <option value="dhl" @selected($shipment->carrier === 'dhl')>DHL</option>
                                                </select>
                                            </div>
                                            <select name="status" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                                <option value="pendiente_envio" @selected($shipment->status === 'pendiente_envio')>Pendiente envío</option>
                                                <option value="preparacion" @selected($shipment->status === 'preparacion')>Preparación</option>
                                                <option value="en_ruta" @selected($shipment->status === 'en_ruta')>En ruta</option>
                                                <option value="entregado" @selected($shipment->status === 'entregado')>Entregado</option>
                                            </select>
                                            <input type="date" name="eta_date" value="{{ $shipment->eta_date }}" class="rounded-lg border border-slate-200 px-2 py-1 text-xs" />
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-ss-emerald text-white text-xs font-semibold">Actualizar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-slate-500">No hay envíos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section data-section="inventario" class="hidden space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Inventario OneCard</p>
                        <h3 class="text-lg font-semibold text-slate-900">Stock disponible</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3">Stock actual</th>
                                <th class="px-6 py-3">Pendiente (sol.)</th>
                                <th class="px-6 py-3">Proyección</th>
                                <th class="px-6 py-3">Mínimo</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($products ?? []) as $product)
                                @php
                                    $isCritical = $product->isStockCritical();
                                    $pending = ($pendingByProduct[$product->id] ?? 0);
                                    $projected = $product->stock_current - $pending;
                                    $projectedCritical = $projected <= $product->stock_minimum;
                                @endphp
                                <tr class="border-t border-slate-100">
                                    <td class="px-6 py-3 font-semibold text-slate-900">{{ $product->name }}</td>
                                    <td class="px-6 py-3">{{ $product->stock_current }}</td>
                                    <td class="px-6 py-3 text-amber-600 font-semibold">{{ $pending }}</td>
                                    <td class="px-6 py-3 {{ $projectedCritical ? 'text-amber-700 font-semibold' : 'text-slate-700' }}">
                                        {{ $projected }}
                                    </td>
                                    <td class="px-6 py-3">{{ $product->stock_minimum }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isCritical ? 'bg-amber-100 text-amber-700' : 'bg-ss-emerald/15 text-ss-emerald' }}">
                                            {{ $isCritical ? 'Crítico' : 'Sano' }}
                                        </span>
                                        @if($pending > 0)
                                            <span class="ml-2 px-2 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">Pend: {{ $pending }}</span>
                                        @endif
                                        @if($projectedCritical)
                                            <span class="ml-2 px-2 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">Proy. bajo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="flex flex-col gap-2 md:flex-row md:items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="stock_current" value="{{ $product->stock_current }}" min="0" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <input type="number" name="stock_minimum" value="{{ $product->stock_minimum }}" min="0" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-ss-emerald text-white text-xs font-semibold">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 mt-4">
                    <div class="px-6 py-4">
                        <p class="text-sm text-slate-500">Stock inteligente (demo)</p>
                        <h3 class="text-lg font-semibold text-slate-900">Proyección por empresa</h3>
                        <p class="text-xs text-slate-500 mt-1">Datos ficticios para ilustrar demanda mensual, tarjetas por vencer y reposición sugerida.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-700" id="smart-stock-table">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-6 py-3">Empresa</th>
                                    <th class="px-6 py-3">Demanda mensual (prom.)</th>
                                    <th class="px-6 py-3">Vencen en 30 días</th>
                                    <th class="px-6 py-3">Vencidas</th>
                                    <th class="px-6 py-3">Stock sugerido</th>
                                    <th class="px-6 py-3">Cobertura estimada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="smart-stock-body">
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-slate-500">Calculando proyección…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </section>
        </section>
    </main>
</div>

{{-- Modal de solicitud para admin --}}
<div id="admin-request-modal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center px-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-4 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Detalle de solicitud</p>
                <h3 id="modal-req-title" class="text-lg font-semibold text-slate-900">Solicitud</h3>
            </div>
            <button id="admin-close-modal" class="text-slate-500 hover:text-slate-800">✕</button>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm text-slate-700">
            <div><span class="text-slate-500">Producto:</span> <span id="modal-req-product" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Contrato:</span> <span id="modal-req-contract" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Motivo:</span> <span id="modal-req-reason" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Cantidad:</span> <span id="modal-req-quantity" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Notas del cliente:</span> <span id="modal-req-notes" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Estado actual:</span> <span id="modal-req-status" class="font-semibold"></span></div>
            <div><span class="text-slate-500">Tracking:</span> <span id="modal-req-tracking" class="font-semibold text-ss-emerald"></span></div>
            <div><span class="text-slate-500">Nota admin:</span> <span id="modal-req-admin-note" class="font-semibold"></span></div>
        </div>
        <div id="modal-contract-summary" class="grid grid-cols-5 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
            <div><p class="text-slate-500">Límite</p><p id="modal-contract-limit" class="text-lg font-semibold text-slate-900">-</p></div>
            <div><p class="text-slate-500">En uso</p><p id="modal-contract-current" class="text-lg font-semibold text-slate-900">-</p></div>
            <div><p class="text-slate-500">Inactivas</p><p id="modal-contract-inactive" class="text-lg font-semibold text-slate-900">-</p></div>
            <div><p class="text-slate-500">Vencidas</p><p id="modal-contract-expired" class="text-lg font-semibold text-slate-900">-</p></div>
            <div><p class="text-slate-500">Disponibles</p><p id="modal-contract-available" class="text-lg font-semibold text-ss-emerald">-</p></div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <form id="modal-approve-form" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm">Aprobar</button>
            </form>
            <form id="modal-reject-form" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <input type="text" name="admin_note" placeholder="Motivo de rechazo" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold shadow-sm">Rechazar</button>
            </form>
        </div>
        <div id="modal-warn" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            No aplicable: la cantidad excede el disponible para nuevas asignaciones.
        </div>
    </div>
</div>

{{-- Modal de alta de empresa y contratos --}}
<div id="client-modal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center px-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Nueva empresa</p>
                <h3 class="text-lg font-semibold text-slate-900">Crear con contrato base</h3>
            </div>
            <button id="client-close-modal" class="text-slate-500 hover:text-slate-800">✕</button>
        </div>
        <form action="{{ route('admin.clients.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-slate-600">Nombre de la empresa</label>
                <input type="text" name="name" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="text-sm text-slate-600">Nombre del contrato</label>
                <input type="text" name="contract_name" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. Contrato principal" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach (($products ?? collect())->whereIn('name', ['Combustible', 'Despensa', 'Premios']) as $product)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                        <label class="text-xs text-slate-500">Límite de contrato</label>
                        <input type="number" name="limits[{{ $product->name }}]" min="0" class="w-full mt-1 rounded-lg border border-slate-200 px-2 py-1 text-sm" placeholder="Ej. 200">
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="client-cancel" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm">Crear empresa</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de alta de contrato --}}
<div id="contract-modal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center px-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Nuevo contrato</p>
                <h3 class="text-lg font-semibold text-slate-900">Agregar productos y límite</h3>
            </div>
            <button id="contract-close-modal" class="text-slate-500 hover:text-slate-800">✕</button>
        </div>
        <form id="contract-form" action="{{ route('admin.clients.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="existing_client" value="1">
            <input type="hidden" name="client_id" id="contract-client-id">
            <div>
                <label class="text-sm text-slate-600">Nombre del contrato</label>
                <input type="text" name="contract_name" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Ej. Contrato adicional" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach (($products ?? collect())->whereIn('name', ['Combustible', 'Despensa', 'Premios']) as $product)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                        <label class="text-xs text-slate-500">Límite de contrato</label>
                        <input type="number" name="limits[{{ $product->name }}]" min="0" class="w-full mt-1 rounded-lg border border-slate-200 px-2 py-1 text-sm" placeholder="Ej. 200">
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="contract-cancel" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm">Crear contrato</button>
            </div>
        </form>
    </div>
</div>
@if ($clientData->isNotEmpty())
<script>
    const clientsData = @json($clientData);
    const requestsByClient = @json($requestsByClient ?? []);
    const allRequests = @json($allRequests ?? []);
    (function () {
        const buttons = document.querySelectorAll('[data-client-id]');
        let selectedId = clientsData.length ? clientsData[0].id : null;

        const nameEl = document.getElementById('detail-name');
        const contractCountEl = document.getElementById('detail-contract-count');
        const inUseEl = document.getElementById('detail-in-use');
        const inactiveEl = document.getElementById('detail-inactive');
        const limitEl = document.getElementById('detail-limit');
        const availableEl = document.getElementById('detail-available');
        const contractsBody = document.getElementById('contracts-body');
        const requestsContainer = document.getElementById('requests-container');
        const navButtons = document.querySelectorAll('button[data-view]');
        const sections = document.querySelectorAll('[data-section]');
        const allowedViews = ['empresas', 'solicitudes', 'envios', 'inventario'];
        const storedView = localStorage.getItem('adminActiveView');
        const defaultView = allowedViews.includes(storedView) ? storedView : 'empresas';
        const smartStockBody = document.getElementById('smart-stock-body');

        const smartStockDemo = [
            { empresa: 'Grupo Johnsons', demanda: 280, vencen: 60, vencidas: 35, stockSugerido: 320 },
            { empresa: 'Tegna Inc', demanda: 180, vencen: 40, vencidas: 22, stockSugerido: 210 },
            { empresa: 'ACME Corp', demanda: 140, vencen: 25, vencidas: 10, stockSugerido: 160 },
        ];

        const renderSmartStock = () => {
            if (!smartStockBody) return;
            smartStockBody.innerHTML = '';
            smartStockDemo.forEach(item => {
                const cobertura = item.stockSugerido > 0 ? (item.stockSugerido / item.demanda).toFixed(1) : '0';
                const row = document.createElement('tr');
                row.className = 'border-t border-slate-100';
                row.innerHTML = `
                    <td class="px-6 py-3 font-semibold text-slate-900">${item.empresa}</td>
                    <td class="px-6 py-3">${item.demanda} tarjetas/mes</td>
                    <td class="px-6 py-3 text-amber-700 font-semibold">${item.vencen}</td>
                    <td class="px-6 py-3 text-amber-600">${item.vencidas}</td>
                    <td class="px-6 py-3 text-ss-emerald font-semibold">${item.stockSugerido}</td>
                    <td class="px-6 py-3 text-slate-700">~${cobertura} meses</td>
                `;
                smartStockBody.appendChild(row);
            });
        };

        const modal = document.getElementById('admin-request-modal');
        const modalClose = document.getElementById('admin-close-modal');
        const modalTitle = document.getElementById('modal-req-title');
        const modalProduct = document.getElementById('modal-req-product');
        const modalContract = document.getElementById('modal-req-contract');
        const modalReason = document.getElementById('modal-req-reason');
        const modalQuantity = document.getElementById('modal-req-quantity');
        const modalNotes = document.getElementById('modal-req-notes');
        const modalStatus = document.getElementById('modal-req-status');
        const modalTracking = document.getElementById('modal-req-tracking');
        const modalAdminNote = document.getElementById('modal-req-admin-note');
        const modalLimit = document.getElementById('modal-contract-limit');
        const modalCurrent = document.getElementById('modal-contract-current');
        const modalInactive = document.getElementById('modal-contract-inactive');
        const modalExpired = document.getElementById('modal-contract-expired');
        const modalAvailable = document.getElementById('modal-contract-available');
        const approveForm = document.getElementById('modal-approve-form');
        const rejectForm = document.getElementById('modal-reject-form');
        const rejectNoteInput = rejectForm.querySelector('input[name="admin_note"]');
        const modalWarn = document.getElementById('modal-warn');
        const listPanel = document.getElementById('client-list');
        const detailPanel = document.getElementById('client-detail');
        const backBtn = document.getElementById('back-to-list');
        const contractsAccordion = document.getElementById('contracts-accordion');
        const flash = document.getElementById('flash-success');
        const allRequestsContainer = document.getElementById('all-requests-container');
        const filterStatus = document.getElementById('filter-status');
        const filterClient = document.getElementById('filter-client');
        const clientModal = document.getElementById('client-modal');
        const openClientModal = document.getElementById('open-client-modal');
        const closeClientModal = document.getElementById('client-close-modal');
        const cancelClientModal = document.getElementById('client-cancel');
        const contractModal = document.getElementById('contract-modal');
        const contractClose = document.getElementById('contract-close-modal');
        const contractCancel = document.getElementById('contract-cancel');
        const contractClientInput = document.getElementById('contract-client-id');
        const openContractButtons = document.querySelectorAll('[data-open-contract-modal]');

        function render(clientId) {
            const client = clientsData.find(c => c.id === clientId);
            if (!client) return;
            selectedId = clientId;

            listPanel.classList.add('hidden');
            detailPanel.classList.remove('hidden');
            detailPanel.classList.remove('lg:col-span-2');
            detailPanel.classList.add('col-span-3');
            backBtn.classList.remove('hidden');
            document.querySelectorAll('[data-open-contract-modal]').forEach(btn => btn.setAttribute('data-client-id', client.id));

            nameEl.textContent = client.name;
            contractCountEl.textContent = `${client.summary.contracts} contratos`;
            inUseEl.textContent = client.summary.in_use;
            inactiveEl.textContent = client.summary.inactive;
            limitEl.textContent = client.summary.limit;
            availableEl.textContent = client.summary.available;

            contractsAccordion.innerHTML = '';
            if (!client.contracts.length) {
                contractsAccordion.innerHTML = `<div class="px-6 py-4 text-center text-slate-500">Sin contratos asignados.</div>`;
            } else {
                client.contracts.forEach(contract => {
                    const details = document.createElement('details');
                    details.className = 'group open:bg-slate-50';
                    details.innerHTML = `
                        <summary class="flex items-center justify-between px-6 py-4 cursor-pointer">
                            <div>
                                <p class="text-sm text-slate-500">Contrato #${contract.id}</p>
                                <h3 class="text-lg font-semibold text-slate-900">${contract.name || 'Contrato'} · Productos: ${contract.allocations.length}</h3>
                            </div>
                            <div class="flex items-center gap-6 text-sm">
                                <span class="text-slate-600">Límite: <strong>${contract.limit}</strong></span>
                                <span class="text-slate-600">En uso: <strong>${contract.current}</strong></span>
                                <span class="text-slate-600">Disponibles: <strong class="text-ss-emerald">${contract.available_new ?? contract.available}</strong></span>
                            </div>
                        </summary>
                        <div class="px-6 pb-6">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-slate-700">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3">Producto</th>
                                            <th class="px-4 py-3">Límite</th>
                                            <th class="px-4 py-3">En uso</th>
                                            <th class="px-4 py-3">Inactivas</th>
                                            <th class="px-4 py-3">Vencidas</th>
                                            <th class="px-4 py-3">Disponible para nuevos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${contract.allocations.map(allocation => `
                                            <tr class="border-t border-slate-100">
                                                <td class="px-4 py-3 font-semibold text-slate-900">${allocation.product}</td>
                                                <td class="px-4 py-3">${allocation.limit}</td>
                                                <td class="px-4 py-3">${allocation.current}</td>
                                                <td class="px-4 py-3">${allocation.inactive}</td>
                                                <td class="px-4 py-3 text-amber-600 font-semibold">${allocation.expired}</td>
                                                <td class="px-4 py-3 text-ss-emerald font-semibold">${Math.max(0, allocation.limit - allocation.current - allocation.expired)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                    contractsAccordion.appendChild(details);
                });
            }

            buttons.forEach(btn => {
                const isActive = Number(btn.dataset.clientId) === clientId;
                btn.classList.toggle('border-ss-emerald', isActive);
                btn.classList.toggle('bg-ss-emerald/10', isActive);
                btn.classList.toggle('hover:bg-ss-emerald/10', !isActive);
            });

            renderRequests(clientId);
        }

        function renderRequests(clientId) {
            const list = requestsByClient[clientId] || [];
            requestsContainer.innerHTML = '';

            if (!list.length) {
                requestsContainer.innerHTML = `<div class="px-6 py-4 text-center text-slate-500">No hay solicitudes para esta empresa.</div>`;
                return;
            }

            list.forEach(req => {
                const wrapper = document.createElement('div');
                wrapper.className = 'px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-slate-50';
                wrapper.setAttribute('data-req-id', req.id);
                wrapper.innerHTML = `
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-slate-900">${req.client_name || 'Empresa'} · Solicitud #${req.id} · ${req.product}</p>
                        <p class="text-xs text-slate-600">Motivo: ${reasonLabel(req.reason)} · Cantidad: ${req.quantity} · Contrato #${req.contract ?? '-'}</p>
                        <p class="text-xs text-slate-500">Estado: ${req.status} ${req.admin_note ? ' · Nota: ' + req.admin_note : ''}</p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full bg-slate-100 text-slate-700">${capitalize(req.status)}</span>
                `;
                wrapper.addEventListener('click', () => openModal(req));
                requestsContainer.appendChild(wrapper);
            });
        }

        function openModal(req) {
            modalTitle.textContent = `Solicitud #${req.id}`;
            modalProduct.textContent = req.product;
            modalContract.textContent = req.contract ?? '-';
            modalReason.textContent = reasonLabel(req.reason);
            modalQuantity.textContent = req.quantity;
            modalNotes.textContent = req.notes || '—';
            modalStatus.textContent = capitalize(req.status);
            rejectNoteInput.value = req.admin_note || '';
            modalTracking.textContent = req.tracking ? req.tracking : '—';
            modalAdminNote.textContent = req.admin_note || '—';

            if (req.contract_info) {
                modalLimit.textContent = req.contract_info.limit;
                modalCurrent.textContent = req.contract_info.current;
                modalInactive.textContent = req.contract_info.inactive;
                modalExpired.textContent = req.contract_info.expired;
                modalAvailable.textContent = req.contract_info.available;
            } else {
                modalLimit.textContent = '-';
                modalCurrent.textContent = '-';
                modalInactive.textContent = '-';
                modalExpired.textContent = '-';
                modalAvailable.textContent = '-';
            }

            approveForm.setAttribute('action', `/admin/requests/${req.id}/status`);
            rejectForm.setAttribute('action', `/admin/requests/${req.id}/status`);

            const needWarn = req.reason === 'new_employee' && req.contract_info && req.quantity > req.contract_info.available_new;
            if (needWarn) {
                modalWarn.classList.remove('hidden');
                modalWarn.textContent = 'No aplicable: la cantidad excede el disponible para nuevas asignaciones (considerando inactivas).';
            } else {
                modalWarn.classList.add('hidden');
                modalWarn.textContent = '';
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function reasonLabel(reason) {
            switch(reason) {
                case 'expired': return 'Reposición por vencimiento';
                case 'lost': return 'Reposición por pérdida';
                case 'new_employee': return 'Nuevos empleados';
                default: return reason;
            }
        }

        function capitalize(text) {
            if (!text) return '';
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        function activateView(view) {
            navButtons.forEach(b => {
                const isActive = b.getAttribute('data-view') === view;
                b.classList.toggle('bg-white/10', isActive);
                b.classList.toggle('text-white', isActive);
                b.classList.toggle('text-slate-300', !isActive);
            });
            sections.forEach(sec => {
                if (sec.getAttribute('data-section') === view) {
                    sec.classList.remove('hidden');
                } else {
                    sec.classList.add('hidden');
                }
            });
            localStorage.setItem('adminActiveView', view);
        }

        navButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.getAttribute('data-view');
                activateView(view);
            });
        });

        activateView(defaultView);

        function renderAllRequests() {
            if (!allRequestsContainer) return;
            const statusVal = filterStatus?.value || 'all';
            const clientVal = filterClient?.value || 'all';
            const list = allRequests.filter(req => {
                const matchStatus = statusVal === 'all' || req.status === statusVal;
                const matchClient = clientVal === 'all' || String(req.client_id) === clientVal;
                return matchStatus && matchClient;
            });

            allRequestsContainer.innerHTML = '';
            if (!list.length) {
                allRequestsContainer.innerHTML = `<div class="px-6 py-4 text-center text-slate-500">No hay solicitudes con ese filtro.</div>`;
                return;
            }

            list.forEach(req => {
                const row = document.createElement('div');
                row.className = 'px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-slate-50';
                row.innerHTML = `
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-slate-900">${req.client_name || 'Empresa'} · Solicitud #${req.id} · ${req.product}</p>
                        <p class="text-xs text-slate-600">Motivo: ${reasonLabel(req.reason)} · Cantidad: ${req.quantity} · Contrato #${req.contract ?? '-'}</p>
                        <p class="text-xs text-slate-500">Estado: ${capitalize(req.status)} ${req.admin_note ? ' · Nota: ' + req.admin_note : ''}</p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full bg-slate-100 text-slate-700">${capitalize(req.status)}</span>
                `;
                row.addEventListener('click', () => openModal(req));
                allRequestsContainer.appendChild(row);
            });
        }

        filterStatus?.addEventListener('change', renderAllRequests);
        filterClient?.addEventListener('change', renderAllRequests);
        renderAllRequests();
        renderSmartStock();

        openContractButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const clientId = btn.getAttribute('data-client-id');
                if (contractClientInput) contractClientInput.value = clientId;
                contractModal?.classList.remove('hidden');
            });
        });

        const closeContract = () => contractModal?.classList.add('hidden');
        contractClose?.addEventListener('click', closeContract);
        contractCancel?.addEventListener('click', closeContract);
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeContract(); });

        if (flash) {
            setTimeout(() => flash.classList.add('hidden'), 1000);
        }

        const openClient = () => clientModal?.classList.remove('hidden');
        const closeClient = () => clientModal?.classList.add('hidden');
        openClientModal?.addEventListener('click', openClient);
        closeClientModal?.addEventListener('click', closeClient);
        cancelClientModal?.addEventListener('click', closeClient);
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeClient(); });

        backBtn?.addEventListener('click', () => {
            listPanel.classList.remove('hidden');
            detailPanel.classList.add('hidden');
            detailPanel.classList.remove('col-span-3');
            detailPanel.classList.add('lg:col-span-2');
            contractsAccordion.innerHTML = `<div class="px-6 py-4 text-center text-slate-500">Selecciona una empresa para ver sus contratos.</div>`;
            nameEl.textContent = 'Seleccione una empresa';
            contractCountEl.textContent = '- contratos';
            inUseEl.textContent = '-';
            inactiveEl.textContent = '-';
            limitEl.textContent = '-';
            availableEl.textContent = '-';
            backBtn.classList.add('hidden');
        });

        modalClose?.addEventListener('click', closeModal);
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

        window.selectClient = render;
    })();
</script>
@endif
@endsection
