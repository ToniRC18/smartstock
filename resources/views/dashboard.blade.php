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
            <a class="block px-3 py-2 rounded-lg bg-white/10 text-white font-medium">Dashboard</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Pedidos</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Inventario</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Reportes</a>
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
            <button class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm hover:-translate-y-0.5 transition transform">
                Nuevo pedido
            </button>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['title' => 'Tarjetas en uso', 'value' => '128', 'tone' => 'bg-white'],
                    ['title' => 'Tarjetas sin uso', 'value' => '54', 'tone' => 'bg-white'],
                    ['title' => 'Tarjetas vencidas', 'value' => '9', 'tone' => 'bg-amber-50'],
                    ['title' => 'Stock disponible', 'value' => '210', 'tone' => 'bg-ss-emerald/10'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="rounded-xl border border-slate-200 {{ $card['tone'] }} p-5 shadow-sm">
                    <p class="text-sm text-slate-500">{{ $card['title'] }}</p>
                    <p class="text-3xl font-semibold text-slate-900 mt-2">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <p class="text-sm text-slate-500">Pedidos recientes</p>
                    <h2 class="text-lg font-semibold text-slate-900">Resumen</h2>
                </div>
                <button class="text-sm text-ss-emerald font-semibold">Ver todo</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Pedido</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Tarjetas</th>
                            <th class="px-6 py-3">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-3 font-semibold text-slate-900">#A-2031</td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-ss-emerald/15 text-ss-emerald">En ruta</span>
                            </td>
                            <td class="px-6 py-3">60</td>
                            <td class="px-6 py-3 text-slate-500">12/08</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-3 font-semibold text-slate-900">#A-2030</td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Preparación</span>
                            </td>
                            <td class="px-6 py-3">30</td>
                            <td class="px-6 py-3 text-slate-500">11/08</td>
                        </tr>
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-3 font-semibold text-slate-900">#A-2029</td>
                            <td class="px-6 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-600">Atención</span>
                            </td>
                            <td class="px-6 py-3">12</td>
                            <td class="px-6 py-3 text-slate-500">10/08</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
