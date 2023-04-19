<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldToServiceTypesTable extends Migration
{

    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('admin_fee')->nullable();
        });
    }


    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
        });
    }
}
