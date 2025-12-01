{{-- Dashboard para administradores con indicadores globales y tabla de clientes --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-slate-100">
    <aside class="w-64 bg-ss-dark text-white flex flex-col border-r border-slate-800">
        <div class="px-6 py-6 border-b border-slate-800">
            <p class="text-lg font-semibold">SmartStock</p>
            <p class="text-sm text-slate-300">Panel administrador</p>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-3 text-sm">
            <a class="block px-3 py-2 rounded-lg bg-white/10 text-white font-medium">Dashboard</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Clientes</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Stock global</a>
            <a class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/5">Alertas</a>
        </nav>
        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            Supervisión central
        </div>
    </aside>

    <main class="flex-1 p-8 space-y-8">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Estado general</p>
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard Admin</h1>
            </div>
            <button class="px-4 py-2 rounded-lg bg-ss-emerald text-white text-sm font-semibold shadow-sm hover:-translate-y-0.5 transition transform">
                Crear alerta
            </button>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Stock global</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">5,430</p>
                <p class="text-xs text-slate-500 mt-1">Disponible para asignación</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Pedidos activos</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">48</p>
                <p class="text-xs text-slate-500 mt-1">En seguimiento</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Alertas críticas</p>
                <p class="text-3xl font-semibold text-amber-600 mt-2">3</p>
                <p class="text-xs text-amber-600 mt-1">Requiere acción inmediata</p>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <p class="text-sm text-slate-500">Clientes</p>
                        <h2 class="text-lg font-semibold text-slate-900">Tabla de clientes</h2>
                    </div>
                    <button class="text-sm text-ss-emerald font-semibold">Ver todos</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Segmento</th>
                                <th class="px-6 py-3">Pedidos</th>
                                <th class="px-6 py-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-slate-100">
                                <td class="px-6 py-3 font-semibold text-slate-900">Nova Bank</td>
                                <td class="px-6 py-3 text-slate-600">Finanzas</td>
                                <td class="px-6 py-3">32</td>
                                <td class="px-6 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-ss-emerald/15 text-ss-emerald">Activo</span>
                                </td>
                            </tr>
                            <tr class="border-t border-slate-100">
                                <td class="px-6 py-3 font-semibold text-slate-900">LogiMax</td>
                                <td class="px-6 py-3 text-slate-600">Logística</td>
                                <td class="px-6 py-3">21</td>
                                <td class="px-6 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Observación</span>
                                </td>
                            </tr>
                            <tr class="border-t border-slate-100">
                                <td class="px-6 py-3 font-semibold text-slate-900">Retailia</td>
                                <td class="px-6 py-3 text-slate-600">Retail</td>
                                <td class="px-6 py-3">18</td>
                                <td class="px-6 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-600">Atención</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Alertas del sistema</p>
                        <h2 class="text-lg font-semibold text-slate-900">Estado</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">En vivo</span>
                </div>
                <div class="space-y-3">
                    <div class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Stock crítico en almacén norte.
                    </div>
                    <div class="rounded-lg border border-ss-emerald/20 bg-ss-emerald/10 px-4 py-3 text-sm text-ss-emerald">
                        Pedidos fuera de límite controlados.
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        Sistema estable, sin interrupciones.
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
