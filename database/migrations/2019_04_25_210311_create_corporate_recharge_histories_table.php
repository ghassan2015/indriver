<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporateRechargeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_recharge_histories', function (Blueprint $table) {
            $table->increments('id'); 
            $table->integer('corporate_id'); 
            $table->enum('recharge_option', [
                    'PREPAID',
                    'POSTPAID'
                ]);
            $table->float('amount',10,2)->default(0); 
            $table->enum('payment_status', [
                    'NONE',
                    'PAID',
                    'NOTPAID'
                ])->default('NONE'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corporate_recharge_histories');
    }
}
