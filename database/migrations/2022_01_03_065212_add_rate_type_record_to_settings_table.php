<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRateTypeRecordToSettingsTable extends Migration
{

    public function up()
    {
        $setting = new \App\Settings();
        $setting->key = 'rate_type';
        $setting->value = \App\UserRequestPayment::FLAT;
        $setting->save();
    }


}
