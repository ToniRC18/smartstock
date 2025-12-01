<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('card_limit_amount')->default(0);
            $table->unsignedInteger('card_current_amount')->default(0);
            $table->unsignedInteger('card_inactive_amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contracts');
    }
};
