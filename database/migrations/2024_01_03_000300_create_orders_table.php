<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('cantidad_solicitada');
            $table->unsignedInteger('cantidad_aprobada');
            $table->enum('estado', ['pendiente', 'rechazado', 'preparando', 'en_ruta', 'entregado'])->default('pendiente');
            $table->string('motivo_rechazo')->nullable();
            $table->string('codigo_tracking')->unique();
            $table->date('fecha_estimada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
