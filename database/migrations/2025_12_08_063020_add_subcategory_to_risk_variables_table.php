<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('risk_variables', function (Blueprint $table) {
        $table->string('subcategory')->nullable()->after('variable_name');
    });
}

public function down()
{
    Schema::table('risk_variables', function (Blueprint $table) {
        $table->dropColumn('subcategory');
    });
}

};
