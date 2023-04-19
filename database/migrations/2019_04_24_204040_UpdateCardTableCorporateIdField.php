<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCardTableCorporateIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporates', function (Blueprint $table) { 
            $table->string('stripe_cust_id')->nullable()->after('recharge_option');
        });
        Schema::table('cards', function (Blueprint $table) { 
            $table->integer('corporate_id')->default(0)->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('corporate_id');
        });
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn('stripe_cust_id');
        });
    }
}
