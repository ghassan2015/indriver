<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentObjectIntoUserRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->text('dispatcher_payments')->nullable();
            $table->enum('request_type', ['ride', 'dispatch'])->default('ride');
        });
    }


    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn('dispatcher_payments');
            $table->dropColumn('request_type');
        });
    }
}
