<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPerKilometerIntoUserRequestPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->double('per_kilometer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->$table->dropColumn('per_kilometer');
        });
    }
}
