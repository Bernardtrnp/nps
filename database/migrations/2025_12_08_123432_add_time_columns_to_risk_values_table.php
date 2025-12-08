<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Jika kolom year SUDAH ADA → hentikan migration sepenuhnya
        if (Schema::hasColumn('risk_values', 'year')) {
            return;
        }

        Schema::table('risk_values', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_values', 'year')) {
                $table->integer('year')->nullable();
            }
            if (!Schema::hasColumn('risk_values', 'quarter')) {
                $table->integer('quarter')->nullable();
            }
            if (!Schema::hasColumn('risk_values', 'month')) {
                $table->integer('month')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('risk_values', 'year')) return;

        Schema::table('risk_values', function (Blueprint $table) {
            $table->dropColumn(['year', 'quarter', 'month']);
        });
    }
};
