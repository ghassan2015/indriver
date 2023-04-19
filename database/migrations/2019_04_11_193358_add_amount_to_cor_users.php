<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountToCorUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cor_users', function (Blueprint $table) {
            $table->float('payamount',8,2)->default(0.00)->after('password');           
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cor_users', function (Blueprint $table) {
            $table->dropColumn('payamount');
            
        });
    }
}
