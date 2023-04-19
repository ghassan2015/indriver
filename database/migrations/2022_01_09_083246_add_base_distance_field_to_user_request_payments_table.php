<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBaseDistanceFieldToUserRequestPaymentsTable extends Migration
{

    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->double('base_distance')->nullable();
        });
    }


    public function down()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->$table->dropColumn('base_distance');
        });
    }
}
