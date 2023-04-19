<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUserRequestPayments extends Migration
{

    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->string('rate_type')->default('Distance Calculation');
            $table->string('flat_rate')->nullable();
        });
    }


    public function down()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn('rate_type');
            $table->dropColumn('flat_rate');
        });
    }
}
