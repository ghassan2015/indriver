<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserrequestSchedulesStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn('status');   
        });
        Schema::table('user_requests', function (Blueprint $table) { 
            $table->enum('status', [
                    'SEARCHING',
                    'CANCELLED',
                    'ACCEPTED', 
                    'STARTED',
                    'ARRIVED',
                    'PICKEDUP',
                    'DROPPED',
                    'COMPLETED',
                    'SCHEDULED',
                    'SCHEDULES',
                ])->after('rental_hours');
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
            $table->dropColumn('status');
        });
        Schema::table('user_requests', function (Blueprint $table) { 
            $table->enum('status', [
                    'SEARCHING',
                    'CANCELLED',
                    'ACCEPTED', 
                    'STARTED',
                    'ARRIVED',
                    'PICKEDUP',
                    'DROPPED',
                    'COMPLETED',
                    'SCHEDULED',
                ]);
        });
    }
}
