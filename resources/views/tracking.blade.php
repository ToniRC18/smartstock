{{-- Vista de tracking con estados dinámicos y datos del envío --}}
@extends('layouts.app')

@section('content')
@php
    $steps = ['Recibido', 'Preparación', 'En ruta', 'Entregado'];
    $statusMap = [
        'recibido' => 1,
        'preparacion' => 2,
        'preparando' => 2,
        'en_ruta' => 3,
        'pendiente' => 1,
        'pendiente_envio' => 1,
        'entregado' => 4,
    ];
    $status = $order?->estado ?? $shipment?->status;
    $currentStep = $status ? ($statusMap[$status] ?? 1) : 1;
@endphp
<div class="min-h-screen bg-slate-50 flex flex-col items-center px-6 py-12">
    <div class="w-full max-w-4xl bg-white border border-slate-200 rounded-2xl shadow-sm p-8 space-y-8">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">ID del pedido</p>
                <h1 class="text-2xl font-semibold text-slate-900">#{{ $order->codigo_tracking ?? $shipment->tracking_code ?? $id }}</h1>
            </div>
            <div class="flex items-center gap-3">
                @if ($status)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-ss-emerald/10 text-ss-emerald capitalize">
                        {{ str_replace('_', ' ', $status) }}
                    </span>
                @endif
                <a href="/" class="text-sm text-ss-emerald font-semibold">Volver al inicio</a>
            </div>
        </header>

        <section class="space-y-3">
            <p class="text-sm font-semibold text-slate-600">Estado</p>
            <div class="flex items-center justify-between">
                @foreach ($steps as $index => $step)
                    <div class="flex-1 flex items-center">
                        <div class="flex flex-col items-center text-center w-28">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-semibold
                                {{ $index + 1 <= $currentStep ? 'bg-ss-emerald text-white' : 'bg-slate-100 text-slate-500' }}">
                                {{ $index + 1 }}
                            </div>
                            <p class="mt-2 text-xs text-slate-600">{{ $step }}</p>
                        </div>
                        @if ($index < count($steps) - 1)
                            <div class="flex-1 h-1 bg-slate-100 mx-2">
                                <div class="h-1 bg-ss-emerald" style="width: {{ $index + 1 < $currentStep ? '100%' : '35%' }}"></div>
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
                    Producto: <strong>{{ $order->product->name ?? $shipment->request->product->name ?? '—' }}</strong>.
                    Estado actual: <strong class="capitalize">{{ str_replace('_', ' ', $status ?? 'pendiente') }}</strong>.
                </p>
                @if ($shipment && $shipment->carrier)
                    <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                        Paquetería: <strong class="capitalize">{{ $shipment->carrier }}</strong>
                    </p>
                @endif
                <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                    Cantidad aprobada: <strong>{{ $order->cantidad_aprobada ?? '—' }}</strong>
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 space-y-3">
                <p class="text-sm text-slate-500">Fecha estimada de entrega</p>
                <p class="text-2xl font-semibold text-slate-900">
                    @if ($order && $order->fecha_estimada)
                        {{ \Illuminate\Support\Carbon::parse($order->fecha_estimada)->translatedFormat('d M') }}
                    @elseif ($shipment && $shipment->eta_date)
                        {{ \Illuminate\Support\Carbon::parse($shipment->eta_date)->translatedFormat('d M') }}
                    @else
                        Pendiente
                    @endif
                </p>
                <p class="text-sm text-slate-600">Se actualizará cuando avance el envío.</p>
            </div>
        </section>
    </div>
</div>
@endsection
