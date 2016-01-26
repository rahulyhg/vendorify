<?php

use Illuminate\Database\Seeder;

class VendorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        // admin(s)
        DB::table('vendors')->insert([
            'email' => 'vendors@vendorify.com',
            'group' => 'admin',
            'name' => 'Vendorify',
            'password' => bcrypt('password'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'business' => 'Vendorify'
        ]);
    }
}
