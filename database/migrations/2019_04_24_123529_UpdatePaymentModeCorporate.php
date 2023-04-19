<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePaymentModeCorporate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
        Schema::table('user_requests', function (Blueprint $table) {
           $table->enum('payment_mode', [
                    'CASH',
                    'CARD',
                    'PAYPAL',
                    'CC_AVENUE',
                    'CORPORATE_ACCOUNT'
                ])->after('cancel_reason'); 
        });
        Schema::table('users', function (Blueprint $table) {
           $table->enum('payment_mode', [
                    'CASH',
                    'CARD',
                    'PAYPAL',
                    'CC_AVENUE',
                    'CORPORATE_ACCOUNT'
                ])->after('last_name'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            //
        });
    }
}
