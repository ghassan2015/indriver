<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWaitingChargesColumnInUserRequestPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            //

            $table->float('waiting_charges', 8, 2)->nullable()->default(0);
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
            $table->dropColumn('waiting_charges');
        });
    }
}
