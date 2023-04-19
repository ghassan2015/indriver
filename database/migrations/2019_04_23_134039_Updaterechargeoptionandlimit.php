<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Updaterechargeoptionandlimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->enum('recharge_option', [
                    'PREPAID',
                    'POSTPAID'
                ])->default('PREPAID')->after('wallet_balance'); 
            $table->float('limit_amount')->default(0)->after('wallet_balance');
            $table->float('deposit_amount')->default(0)->after('wallet_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn('recharge_option');
            $table->dropColumn('limit_amount');
            $table->dropColumn('deposit_amount');
        });
    }
}
