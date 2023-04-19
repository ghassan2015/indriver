<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWaitingTimeFieldToUserRequestsTable extends Migration
{

    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->integer('waiting_time')->default(0);
        });
    }


    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->$table->dropColumn('waiting_time');
        });
    }
}
