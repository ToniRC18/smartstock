{{-- Dashboard básico para usuarios de empresa con sidebar y tarjetas clave --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-slate-100">
    <aside class="w-64 bg-ss-dark text-white flex flex-col border-r border-slate-800">
        <div class="px-6 py-6 border-b border-slate-800">
            <p class="text-lg font-semibold">SmartStock</p>
            <p class="text-sm text-slate-300">Panel empresa</p>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-3 text-sm">
            <button data-view="overview" class="w-full text-left px-3 py-2 rounded-lg bg-white/10 text-white font-medium">Dashboard</button>
            <button data-view="requests" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Solicitudes</button>
            <button data-view="shipments" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Envíos</button>
        </nav>
        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            Sesión segura
        </div>
    </aside>

    <main class="flex-1 p-8 space-y-8">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Visión general</p>
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500">Cliente #{{ $clientId ?? '-' }}</div>
            </div>
        </header>

        <section data-section="overview" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Tarjetas en uso</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">{{ $summary['in_use'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Tarjetas sin uso</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">{{ $summary['inactive'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm text-amber-600">Tarjetas vencidas (reposiciones)</p>
                <p class="text-3xl font-semibold text-amber-700 mt-2">{{ $summary['expired'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Límite de contrato</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">{{ $summary['limit'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-ss-emerald/10 p-5 shadow-sm">
                <p class="text-sm text-ss-emerald">Disponible para nuevos</p>
                <p class="text-3xl font-semibold text-ss-emerald mt-2">{{ $summary['available_for_new'] ?? 0 }}</p>
            </div>
        </section>

        <section data-section="overview" class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <p class="text-sm text-slate-500">Contratos</p>
                    <h2 class="text-lg font-semibold text-slate-900">Detalle y subdivisiones por producto</h2>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($contracts as $contract)
                    <details class="group open:bg-slate-50">
                        <summary class="flex items-center justify-between px-6 py-4 cursor-pointer">
                            <div>
                                <p class="text-sm text-slate-500">Contrato #{{ $contract->id }}</p>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $contract->product->name ?? 'Contrato' }}</h3>
                            </div>
                            <div class="flex items-center gap-6 text-sm">
                                <span class="text-slate-600">Límite: <strong>{{ $contract->card_limit_amount }}</strong></span>
                                <span class="text-slate-600">En uso: <strong>{{ $contract->card_current_amount }}</strong></span>
                                <span class="text-slate-600">Disponibles: <strong class="text-ss-emerald">{{ $contract->availableForNewAssignments() }}</strong></span>
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
                                        @forelse ($contract->allocations as $allocation)
                                            <tr class="border-t border-slate-100">
                                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $allocation->product->name ?? 'Producto' }}</td>
                                                <td class="px-4 py-3">{{ $allocation->card_limit_amount }}</td>
                                                <td class="px-4 py-3">{{ $allocation->card_current_amount }}</td>
                                                <td class="px-4 py-3">{{ $allocation->card_inactive_amount }}</td>
                                                <td class="px-4 py-3 text-amber-600 font-semibold">{{ $allocation->card_expired_amount }}</td>
                                                <td class="px-4 py-3 text-ss-emerald font-semibold">
                                                    {{ max(0, $allocation->card_limit_amount - $allocation->card_current_amount - $allocation->card_expired_amount) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-3 text-center text-slate-500">Sin subdivisiones registradas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="px-6 py-4 text-center text-slate-500">Sin contratos asignados.</div>
                @endforelse
            </div>
        </section>

        <section data-section="requests" class="hidden space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Solicitudes</p>
                    <h3 class="text-lg font-semibold text-slate-900">Reemplazos o nuevas</h3>
                </div>
                <button id="open-request-modal" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm hover:-translate-y-0.5 transition transform">
                    Nueva solicitud
                </button>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Solicitudes recientes</p>
                        <h3 class="text-lg font-semibold text-slate-900">Historial</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Contrato</th>
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3">Motivo</th>
                                <th class="px-6 py-3">Cantidad</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Tracking</th>
                                <th class="px-6 py-3">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr class="border-t border-slate-100">
                                    <td class="px-6 py-3 text-xs text-slate-500">#{{ $request->contract_id }}</td>
                                    <td class="px-6 py-3 font-semibold text-slate-900">{{ $request->product->name ?? 'Producto' }}</td>
                                    <td class="px-6 py-3">
                                        @switch($request->reason)
                                            @case('expired') Reposición por vencimiento @break
                                            @case('lost') Reposición por pérdida @break
                                            @case('new_employee') Nuevos empleados @break
                                            @default {{ $request->reason }}
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-3">{{ $request->quantity }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            @if($request->status === 'approved') bg-ss-emerald/15 text-ss-emerald
                                            @elseif($request->status === 'rejected') bg-amber-100 text-amber-700
                                            @else bg-slate-100 text-slate-700 @endif">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-xs text-slate-600">
                                        @if($request->shipment)
                                            <a class="text-ss-emerald font-semibold" href="/tracking/{{ $request->shipment->tracking_code }}">#{{ $request->shipment->tracking_code }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-xs text-slate-600">{{ $request->admin_note ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-slate-500">Aún no hay solicitudes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section data-section="shipments" class="hidden space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Envíos</p>
                        <h3 class="text-lg font-semibold text-slate-900">Seguimiento</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Tracking</th>
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">ETA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shipments as $shipment)
                                <tr class="border-t border-slate-100">
                                    <td class="px-6 py-3 font-semibold text-slate-900">
                                        <a class="text-ss-emerald" href="/tracking/{{ $shipment->tracking_code }}">#{{ $shipment->tracking_code }}</a>
                                    </td>
                                    <td class="px-6 py-3">{{ $shipment->request->product->name ?? 'Producto' }}</td>
                                    <td class="px-6 py-3 capitalize">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ str_replace('_', ' ', $shipment->status) }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600">{{ $shipment->eta_date ?? 'Pendiente' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-slate-500">Aún no hay envíos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

{{-- Modal de solicitud --}}
<div id="request-modal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center px-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Nueva solicitud</p>
                <h3 class="text-lg font-semibold text-slate-900">Tarjetas corporativas</h3>
            </div>
            <button id="close-request-modal" class="text-slate-500 hover:text-slate-800">✕</button>
        </div>
        <form action="{{ route('dashboard.requests.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="client_id" value="{{ $clientId }}">
            @if ($errors->any())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <div>
                <label class="text-sm text-slate-600">Contrato</label>
                <select name="contract_id" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Selecciona contrato</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract->id }}">Contrato #{{ $contract->id }} - {{ $contract->product->name ?? 'Contrato' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm text-slate-600">Producto</label>
                <select name="product_id" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Selecciona</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-600">Motivo</label>
                    <select name="reason" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                        <option value="">Selecciona</option>
                        <option value="expired">Reposición por vencimiento</option>
                        <option value="lost">Reposición por pérdida</option>
                        <option value="new_employee">Nuevos empleados</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-slate-600">Cantidad</label>
                    <input type="number" name="quantity" min="1" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                </div>
            </div>
            <div>
                <label class="text-sm text-slate-600">Notas</label>
                <input type="text" name="notes" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Detalle opcional (empleados, sucursal, etc.)">
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="cancel-request" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm hover:-translate-y-0.5 transition transform">
                    Enviar solicitud
                </button>
            </div>
            @if (session('status'))
                <p class="text-sm text-ss-emerald font-semibold">{{ session('status') }}</p>
            @endif
        </form>
    </div>
</div>

<script>
    (function () {
        const navButtons = document.querySelectorAll('button[data-view]');
        const sections = document.querySelectorAll('[data-section]');

        navButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.getAttribute('data-view');
                navButtons.forEach(b => b.classList.remove('bg-white/10', 'text-white'));
                navButtons.forEach(b => b.classList.add('text-slate-300'));
                btn.classList.add('bg-white/10', 'text-white');
                btn.classList.remove('text-slate-300');

                sections.forEach(sec => {
                    if (sec.getAttribute('data-section') === view) {
                        sec.classList.remove('hidden');
                    } else {
                        sec.classList.add('hidden');
                    }
                });
            });
        });

        const modal = document.getElementById('request-modal');
        const openModalBtn = document.getElementById('open-request-modal');
        const closeModalBtn = document.getElementById('close-request-modal');
        const cancelBtn = document.getElementById('cancel-request');

        const closeModal = () => modal.classList.add('hidden');
        const openModal = () => modal.classList.remove('hidden');

        openModalBtn?.addEventListener('click', openModal);
        closeModalBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
    })();
</script>
@endsection
