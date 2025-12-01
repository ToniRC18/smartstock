<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_contracts', function (Blueprint $table) {
            $table->unsignedInteger('card_expired_amount')->default(0)->after('card_inactive_amount');
        });
    }

    public function down(): void
    {
        Schema::table('client_contracts', function (Blueprint $table) {
            $table->dropColumn('card_expired_amount');
        });
    }
};
