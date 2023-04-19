<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTableForStops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE user_requests MODIFY d_address VARCHAR(255) NULL;');
        DB::statement('ALTER TABLE user_requests MODIFY d_latitude DOUBLE(15,8) NULL;');
        DB::statement('ALTER TABLE user_requests MODIFY d_longitude DOUBLE(15,8) NULL;');

        Schema::create('user_requests_stops', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_request_id')->nullable(false);
            $table->string('d_address')->nullable();
            $table->double('d_latitude', 15, 8);
            $table->double('d_longitude', 15, 8);
            $table->tinyInteger('order')->nullable();
            $table->enum('status', [
                'PENDING',
                'DROPPED',
                'SKIPPED'
            ])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE user_requests MODIFY d_address VARCHAR(255) NOT NULL;');
        DB::statement('ALTER TABLE user_requests MODIFY d_latitude DOUBLE(15,8) NOT NULL;');
        DB::statement('ALTER TABLE user_requests MODIFY d_longitude DOUBLE(15,8) NOT NULL;');

        Schema::dropIfExists('user_requests_stops');
    }
}
