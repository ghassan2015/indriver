<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinuteToUserRequestPayments extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->float('minute',10,2)->default(0)->after('distance');
            $table->float('hour',10,2)->default(0)->after('minute');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn('minute');
            $table->dropColumn('hour');
        });
    }
}
