<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateServiceTypesCalculationFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
           $table->enum('calculation_format', [ 
                    'TYPEA',
                    'TYPEB',
                    'TYPEC',
                ])->default('TYPEC')->after('image');
            $table->double('between_km',10,2)->default(0)->after('status');
            $table->double('less_distance_price',10,2)->default(0)->after('status');
            $table->double('greater_distance_price',10,2)->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('calculation_format');
            $table->dropColumn('between_km');
            $table->dropColumn('less_distance_price');
            $table->dropColumn('greater_distance_price');
        });
    }
}
