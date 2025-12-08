<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('risk_type_id')->constrained('risk_types')->cascadeOnDelete();

            // new normalized references
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('entitas_id')->nullable()->constrained('entitas')->nullOnDelete();

            $table->string('name');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('risks');
    }
};
