<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Updaterechargepaymentstatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->enum('postpaid_payment_status', [
                    'NONE',
                    'PAID',
                    'NOTPAID'
                ])->default('NONE')->after('status'); 
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
             $table->dropColumn('postpaid_payment_status');
        });
    }
}
