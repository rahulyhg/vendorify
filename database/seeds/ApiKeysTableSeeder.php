<?php

use Illuminate\Database\Seeder;

class ApiKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salt = sha1(time() . mt_rand());
        $newKey = substr($salt, 0, 40);

        DB::table('api_keys')->insert([
            'user_id' => 1,
            'key' => $newKey,
            'level' => 10,
            'ignore_limits' => 1
        ]);
        
    }
}
