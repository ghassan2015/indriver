<?php

use Illuminate\Database\Seeder;

class AddNegWalLimSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->where('key', 'user_negative_wallet_limit')->delete();
        DB::table('settings')->insert([
            [
                'key' => 'user_negative_wallet_limit',
                'value' => '-1000'
            ]
        ]);
    }
}
