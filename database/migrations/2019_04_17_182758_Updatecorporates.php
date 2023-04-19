<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Updatecorporates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->string('otp')->nullable()->after('password'); 
            $table->string('company')->nullable()->after('password');
            $table->string('mobile')->nullable()->after('password');
            $table->string('logo')->nullable()->after('password');
            $table->double('wallet_balance')->default(0)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn('otp');
            $table->dropColumn('company');
            $table->dropColumn('mobile');
            $table->dropColumn('logo');
            $table->dropColumn('wallet_balance');
        });
    }
}
