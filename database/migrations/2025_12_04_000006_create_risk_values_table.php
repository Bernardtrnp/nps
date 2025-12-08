<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('risk_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_variable_id')->constrained('risk_variables')->cascadeOnDelete();

            $table->integer('year');
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->unsignedTinyInteger('month')->nullable();

            $table->decimal('value', 20, 6)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('risk_values');
    }
};
