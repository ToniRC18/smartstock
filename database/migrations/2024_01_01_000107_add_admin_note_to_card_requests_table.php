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
            $table->string('admin_note')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('card_requests', function (Blueprint $table) {
            $table->dropColumn('admin_note');
        });
    }
};
