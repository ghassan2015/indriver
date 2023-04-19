<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBoosterCableIntoUserRequestsTable extends Migration
{
    public function up() {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->boolean( 'is_booster_cable')->nullable();
        });
    }

    public function down() {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->boolean( 'is_booster_cable');
        });
    }
}
