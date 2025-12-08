<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('risk_types', function (Blueprint $table) {
            $table->string('visual_x_mode')->nullable()->after('name');
            $table->string('visual_group_mode')->nullable()->after('visual_x_mode');
        });
    }

    public function down(): void
    {
        Schema::table('risk_types', function (Blueprint $table) {
            $table->dropColumn(['visual_x_mode','visual_group_mode']);
        });
    }
};
