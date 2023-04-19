<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsIntoUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer( 'mobile_verification_code')->nullable();
            $table->dateTime( 'mobile_verification_code_sent_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
	        $table->dropColumn( 'mobile_verification_code');
	        $table->dropColumn( 'mobile_verification_code_sent_at');
        });
    }
}
