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
            <a class="block px-3 py-2 rounded-lg bg-white/10 text-white font-medium">Empresas</a>
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
    </main>
</div>

@if ($clientData->isNotEmpty())
<script>
    const clientsData = @json($clientData);
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
        }

        window.selectClient = render;
        if (selectedId) {
            render(selectedId);
        }
    })();
</script>
@endif
@endsection
