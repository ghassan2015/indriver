<?php

use Illuminate\Database\Seeder;

class AddRideCancelMinutes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->where('key', 'ride_cancellation_minutes')->delete();
        DB::table('settings')->insert([
            [
                'key' => 'ride_cancellation_minutes',
                'value' => '100'
            ]
        ]);
    }
}
