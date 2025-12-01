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
                    'product' => optional($contract->product)->name ?? 'Producto',
                    'limit' => $contract->card_limit_amount,
                    'current' => $contract->card_current_amount,
                    'inactive' => $contract->card_inactive_amount,
                    'available' => $contract->availableByLimit(),
                ];
            })->values(),
        ];
    })->values();
@endphp
<div class="min-h-screen flex bg-slate-100">
    <aside class="w-64 bg-ss-dark text-white flex flex-col border-r border-slate-800">
        <div class="px-6 py-6 border-b border-slate-800">
            <p class="text-lg font-semibold">SmartStock</p>
            <p class="text-sm text-slate-300">Panel admin</p>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-3 text-sm">
            <button data-view="empresas" class="w-full text-left px-3 py-2 rounded-lg bg-white/10 text-white font-medium">Empresas</button>
            <button data-view="solicitudes" class="w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Solicitudes</button>
            <span class="block px-3 py-2 rounded-lg text-slate-400">Alertas</span>
            <span class="block px-3 py-2 rounded-lg text-slate-400">Stock</span>
        </nav>
        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            Supervisión central
        </div>
    </aside>

    <main class="flex-1 p-8 space-y-8">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Empresas en contrato</p>
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard Admin</h1>
            </div>
            <span class="px-4 py-2 rounded-lg bg-ss-emerald/10 text-ss-emerald text-sm font-semibold">
                {{ $clientData->count() }} empresas
            </span>
        </header>

        <section data-section="empresas">
            @if ($clientData->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-10 text-center text-slate-600">
                    Aún no hay clientes cargados. Ejecuta los seeders o agrega contratos para ver la lista.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-1 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-slate-500">Empresas</p>
                                <h2 class="text-lg font-semibold text-slate-900">Selecciona para ver detalles</h2>
                            </div>
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

                    <section class="lg:col-span-2 space-y-6">
                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                                <div>
                                    <p class="text-sm text-slate-500">Detalle de cliente</p>
                                    <h2 id="detail-name" class="text-lg font-semibold text-slate-900">Seleccione una empresa</h2>
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

                        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                                <div>
                                    <p class="text-sm text-slate-500">Contratos del cliente</p>
                                    <h3 class="text-lg font-semibold text-slate-900">Productos y uso</h3>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-slate-700">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-6 py-3">Producto</th>
                                            <th class="px-6 py-3">Límite</th>
                                            <th class="px-6 py-3">En uso</th>
                                            <th class="px-6 py-3">Inactivas</th>
                                            <th class="px-6 py-3">Disponible</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contracts-body">
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">Selecciona una empresa para ver sus contratos.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </section>

        <section data-section="solicitudes" class="hidden space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Solicitudes</p>
                        <h3 class="text-lg font-semibold text-slate-900">Aprobar o rechazar</h3>
                    </div>
                </div>
                <div class="divide-y divide-slate-100" id="requests-container">
                    <div class="px-6 py-4 text-center text-slate-500">Selecciona una empresa para ver solicitudes.</div>
                </div>
            </div>
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
    </div>
</div>

@if ($clientData->isNotEmpty())
<script>
    const clientsData = @json($clientData);
    const requestsByClient = @json($requestsByClient ?? []);
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

        const modal = document.getElementById('admin-request-modal');
        const modalClose = document.getElementById('admin-close-modal');
        const modalTitle = document.getElementById('modal-req-title');
        const modalProduct = document.getElementById('modal-req-product');
        const modalContract = document.getElementById('modal-req-contract');
        const modalReason = document.getElementById('modal-req-reason');
        const modalQuantity = document.getElementById('modal-req-quantity');
        const modalNotes = document.getElementById('modal-req-notes');
        const modalStatus = document.getElementById('modal-req-status');
        const modalLimit = document.getElementById('modal-contract-limit');
        const modalCurrent = document.getElementById('modal-contract-current');
        const modalInactive = document.getElementById('modal-contract-inactive');
        const modalExpired = document.getElementById('modal-contract-expired');
        const modalAvailable = document.getElementById('modal-contract-available');
        const approveForm = document.getElementById('modal-approve-form');
        const rejectForm = document.getElementById('modal-reject-form');

        function render(clientId) {
            const client = clientsData.find(c => c.id === clientId);
            if (!client) return;
            selectedId = clientId;

            nameEl.textContent = client.name;
            contractCountEl.textContent = `${client.summary.contracts} contratos`;
            inUseEl.textContent = client.summary.in_use;
            inactiveEl.textContent = client.summary.inactive;
            limitEl.textContent = client.summary.limit;
            availableEl.textContent = client.summary.available;

            contractsBody.innerHTML = '';
            if (!client.contracts.length) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="px-6 py-4 text-center text-slate-500">Sin contratos asignados.</td>`;
                contractsBody.appendChild(row);
            } else {
                client.contracts.forEach(contract => {
                    const row = document.createElement('tr');
                    row.className = 'border-t border-slate-100';
                    row.innerHTML = `
                        <td class="px-6 py-3 font-semibold text-slate-900">${contract.product}</td>
                        <td class="px-6 py-3">${contract.limit}</td>
                        <td class="px-6 py-3">${contract.current}</td>
                        <td class="px-6 py-3">${contract.inactive}</td>
                        <td class="px-6 py-3 text-ss-emerald font-semibold">${contract.available}</td>
                    `;
                    contractsBody.appendChild(row);
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
                        <p class="text-sm font-semibold text-slate-900">Solicitud #${req.id} · ${req.product}</p>
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

        modalClose?.addEventListener('click', closeModal);
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

        window.selectClient = render;
        if (selectedId) {
            render(selectedId);
        }
    })();
</script>
@endif
@endsection
