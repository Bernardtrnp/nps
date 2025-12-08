<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('risk_types', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->string('visual_x_mode')->nullable()->after('name'); 
            $table->string('visual_group_mode')->nullable()->after('visual_x_mode');

            $table->foreign('parent_id')->references('id')->on('risk_types')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('risk_types', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'visual_x_mode', 'visual_group_mode']);
        });
    }
};
