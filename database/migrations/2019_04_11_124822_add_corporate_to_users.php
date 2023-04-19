<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCorporateToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('company_id')->default(0)->after('otp');
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('emp_id')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('company_name');
            $table->dropColumn('emp_id');
        });
    }
}
