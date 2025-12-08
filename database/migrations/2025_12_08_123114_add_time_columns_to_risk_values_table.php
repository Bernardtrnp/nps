<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('risk_values', function (Blueprint $table) {
            $table->integer('year')->nullable()->after('value');
            $table->integer('quarter')->nullable()->after('year');
            $table->integer('month')->nullable()->after('quarter');
        });
    }

    public function down(): void
    {
        Schema::table('risk_values', function (Blueprint $table) {
            $table->dropColumn(['year','quarter','month']);
        });
    }
};
