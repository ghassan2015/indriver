<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionsIntoUserRequestsTable extends Migration
{
    public function up() {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->text( 'instructions')->nullable();
        });
    }

    public function down() {
        Schema::table('user_requests', function (Blueprint $table) {
	        $table->dropColumn( 'instructions');
        });
    }
}
