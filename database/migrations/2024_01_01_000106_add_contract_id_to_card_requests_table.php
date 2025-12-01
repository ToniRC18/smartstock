<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_requests', function (Blueprint $table) {
            $table->foreignId('contract_id')->nullable()->after('client_id')->constrained('client_contracts');
        });
    }

    public function down(): void
    {
        Schema::table('card_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contract_id');
        });
    }
};
