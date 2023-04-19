<?php

use Illuminate\Database\Seeder;

class FleetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fleets')->truncate();
        DB::table('fleets')->insert([
            'name' => 'Demo',
            'email' => 'fleet@demo.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
