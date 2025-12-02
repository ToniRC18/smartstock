{{-- Vista de landing minimalista como referencia de estilo para todo el proyecto --}}
@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-gradient-to-b from-white via-emerald-50/20 to-slate-50">
    <header class="px-8 md:px-12 py-6 flex items-center justify-between border-b border-slate-200">
        <a href="/" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-ss-emerald/20 border border-ss-emerald flex items-center justify-center text-ss-emerald font-semibold">
                SS
            </div>
            <div>
                <p class="text-xl font-semibold text-slate-900">SmartStock</p>
                <p class="text-sm text-slate-500">Control ágil de tarjetas</p>
            </div>
        </a>
        <nav class="flex items-center gap-3 text-sm font-medium text-slate-700">
            <a href="/dashboard" class="px-3 py-2 rounded-lg hover:text-ss-emerald hover:bg-ss-emerald/10 transition-colors">Dashboard empresas</a>
            <a href="/admin/dashboard" class="px-3 py-2 rounded-lg hover:text-ss-emerald hover:bg-ss-emerald/10 transition-colors">Dashboard admin</a>
        </nav>
    </header>

    <main class="flex-1 flex flex-col gap-14 px-8 md:px-12 lg:px-20 py-12">
        <section class="flex flex-col md:flex-row items-center gap-10">
            <div class="flex-1 space-y-6">
                <p class="inline-flex items-center gap-2 text-sm font-semibold text-ss-emerald bg-ss-emerald/10 px-3 py-1 rounded-full w-max">
                    Flujo sin fricción
                </p>
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-5xl font-bold text-slate-900 leading-tight">
                        Control inteligente de tarjetas corporativas
                    </h1>
                    <p class="text-lg text-slate-600 max-w-2xl">
                        Valida, administra y rastrea tus pedidos sin fricción. Una base ligera para que tu equipo avance rápido y sin obstáculos.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/dashboard" class="px-6 py-3 rounded-lg bg-ss-emerald text-white font-semibold shadow-md shadow-ss-emerald/30 hover:-translate-y-0.5 transition transform">
                        Ir al Dashboard
                    </a>
                    <span class="text-sm text-slate-500">Base limpia lista para extender.</span>
                </div>
            </div>
            <div class="flex-1">
                <div class="relative w-full max-w-lg mx-auto">
                    <div class="absolute inset-0 rounded-3xl bg-ss-emerald/20 blur-3xl"></div>
                    <div class="relative bg-white border border-slate-200 rounded-3xl shadow-xl shadow-slate-200/80 p-8 space-y-5">
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold text-slate-900">Playbook operativo</p>
                            <span class="px-3 py-1 text-xs font-semibold bg-ss-emerald/15 text-ss-emerald rounded-full">Reglas activas</span>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4 bg-slate-50">
                                <div class="h-10 w-10 rounded-lg bg-ss-emerald/15 text-ss-emerald flex items-center justify-center font-semibold">1</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Valida contra contrato</p>
                                    <p class="text-sm text-slate-600">Límite por producto, inactivas y stock mínimo aplicados antes de aprobar.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4 bg-white">
                                <div class="h-10 w-10 rounded-lg bg-ss-emerald/15 text-ss-emerald flex items-center justify-center font-semibold">2</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Stock con alertas</p>
                                    <p class="text-sm text-slate-600">Visualiza críticos y evita aprobar si dejas el almacén en rojo.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4 bg-slate-50">
                                <div class="h-10 w-10 rounded-lg bg-ss-emerald/15 text-ss-emerald flex items-center justify-center font-semibold">3</div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Tracking inmediato</p>
                                    <p class="text-sm text-slate-600">Genera guías y seguimiento al aprobar solicitudes, listo para demo.</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-ss-emerald/10 to-ss-emerald/5 p-4">
                            <p class="text-xs uppercase tracking-wide text-ss-emerald font-semibold">Lista para conectar tus datos reales</p>
                            <p class="text-sm text-slate-600 mt-1">Prototipo con reglas de negocio, validaciones y vistas espejo para cliente y admin.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $features = [
                    [
                        'title' => 'Validación automática',
                        'desc' => 'Verifica tarjetas y pedidos con reglas claras y notificaciones tempranas.',
                    ],
                    [
                        'title' => 'Inventario en tiempo real',
                        'desc' => 'Lecturas precisas de stock con indicadores simples y accionables.',
                    ],
                    [
                        'title' => 'Seguimiento de entrega',
                        'desc' => 'Comunica el estado al cliente con pasos claros y fechas estimadas.',
                    ],
                ];
            @endphp
            @foreach ($features as $feature)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:-translate-y-1 transition transform">
                    <div class="h-10 w-10 rounded-lg bg-ss-emerald/15 text-ss-emerald flex items-center justify-center font-semibold mb-4">
                        {{ strtoupper(substr($feature['title'], 0, 2)) }}
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </section>
    </main>

    <footer class="mt-auto px-8 md:px-12 py-6 border-t border-slate-200 text-sm text-slate-500 flex items-center justify-between">
        <span>SmartStock · Plantilla base</span>
        <span>Diseño claro con colores inspirados en OneCard.mx</span>
    </footer>
</div>
@endsection
