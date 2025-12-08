<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('risk_variables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();

            $table->string('variable_name');

            // identity metadata (optional)
            $table->string('unit_name')->nullable();
            $table->string('entitas_name')->nullable();
            $table->string('project_name')->nullable();

            // analytics metadata
            $table->string('unit_value')->nullable();
            $table->string('value_type')->nullable();
            $table->string('time_dimension')->nullable();

            // other metadata fields
            $table->string('method')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('risk_variables');
    }
};
