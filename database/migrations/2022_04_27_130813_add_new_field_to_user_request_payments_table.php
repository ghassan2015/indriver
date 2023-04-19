<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldToUserRequestPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->string('admin_fee')->nullable();
        });
    }


    public function down()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
        });
    }
}
