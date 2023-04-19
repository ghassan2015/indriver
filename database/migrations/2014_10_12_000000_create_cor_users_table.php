<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cor_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name');
            $table->string('address');
            $table->enum('payment_mode', ['CASH', 'CARD', 'PAYPAL','ELAVON','CAC']);
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->string('picture')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('device_type',array('android','ios'));
            $table->enum('login_by',array('manual','facebook','google', 'apple'));
            $table->string('social_unique_id')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude',15,8)->nullable();
            $table->string('stripe_cust_id')->nullable();
            $table->float('wallet_balance')->default(0);
            $table->decimal('rating', 4, 2)->default(5);
            $table->mediumInteger('otp')->default(0);
            $table->string('language')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('cor_users');
    }
}
