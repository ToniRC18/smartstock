{{-- Vista de tracking sencilla con pasos de entrega y datos del pedido --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col items-center px-6 py-12">
    <div class="w-full max-w-4xl bg-white border border-slate-200 rounded-2xl shadow-sm p-8 space-y-8">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">ID del pedido</p>
                <h1 class="text-2xl font-semibold text-slate-900">#{{ $id }}</h1>
            </div>
            <a href="/" class="text-sm text-ss-emerald font-semibold">Volver al inicio</a>
        </header>

        <section class="space-y-3">
            <p class="text-sm font-semibold text-slate-600">Estado</p>
            @php
                $steps = ['Recibido', 'Preparación', 'En ruta', 'Entregado'];
                $completed = 3;
            @endphp
            <div class="flex items-center justify-between">
                @foreach ($steps as $index => $step)
                    <div class="flex-1 flex items-center">
                        <div class="flex flex-col items-center text-center w-28">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-semibold
                                {{ $index < $completed ? 'bg-ss-emerald text-white' : 'bg-slate-100 text-slate-500' }}">
                                {{ $index + 1 }}
                            </div>
                            <p class="mt-2 text-xs text-slate-600">{{ $step }}</p>
                        </div>
                        @if ($index < count($steps) - 1)
                            <div class="flex-1 h-1 bg-slate-100 mx-2">
                                <div class="h-1 bg-ss-emerald" style="width: {{ $index + 1 < $completed ? '100%' : '35%' }}"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-6">
                <p class="text-sm text-slate-500 mb-2">Detalle</p>
                <p class="text-lg font-semibold text-slate-900">Seguimiento de entrega</p>
                <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                    Este progreso es estático en esta versión. Integra tus propios eventos para actualizar el estado en tiempo real y notificar a tus clientes.
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 space-y-3">
                <p class="text-sm text-slate-500">Fecha estimada de entrega</p>
                <p class="text-2xl font-semibold text-slate-900">Próximo lunes</p>
                <p class="text-sm text-slate-600">Actualiza esta sección con tu proveedor de logística.</p>
            </div>
        </section>
    </div>
</div>
@endsection
