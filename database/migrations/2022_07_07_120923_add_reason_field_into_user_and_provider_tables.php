<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonFieldIntoUserAndProviderTables extends Migration
{
    
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('delete_profile_reason')->nullable();
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->string('delete_profile_reason')->nullable();
        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('delete_profile_reason');
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('delete_profile_reason');
        });
    }
}
