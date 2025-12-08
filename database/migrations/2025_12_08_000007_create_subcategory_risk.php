<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add nullable subcategory column after name (adjust position if needed)
        Schema::table('risks', function (Blueprint $table) {
            if (!Schema::hasColumn('risks', 'subcategory')) {
                $table->string('subcategory')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            if (Schema::hasColumn('risks', 'subcategory')) {
                $table->dropColumn('subcategory');
            }
        });
    }
};
