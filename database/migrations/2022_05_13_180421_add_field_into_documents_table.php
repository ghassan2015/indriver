<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldIntoDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('is_mandatory')->default(0);
        });
    }


    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('is_mandatory');
        });
    }
}
