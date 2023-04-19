<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsMobileVerifiedIntoUsersTable extends Migration
{
    public function up()
    {
	    Schema::table('users', function (Blueprint $table) {
		    $table->boolean('is_mobile_verified')->nullable()->default(0);
	    });
    }

    public function down()
    {
	    Schema::table('users', function (Blueprint $table) {
		    $table->dropColumn('is_mobile_verified');
	    });
    }
}
