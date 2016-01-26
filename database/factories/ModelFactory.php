<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/


// Vendors
$factory->define(App\Vendor::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'business' => $faker->company(),
        'phone' => $faker->phoneNumber(),
        'rent' => rand()*10,
        'rate' => 15,
        'status' => 'active',
        'password' => str_random(10),
        'notes' => '',
        'email_notification' => 1
    ];
});

// Codes
$factory->define(App\Code::class, function ($faker) {
    return [
        'name' => $faker->name
    ];
});

// Payments
$factory->define(App\Payment::class, function ($faker) {
    return [
        'square_id' => str_random(10),
        'total' => rand()*1000,
        'processing_fee' => rand()*5,
        'square_url' => $faker->url(),
        'tender' => 'CREDIT_CARD'
    ];
});

// Transactions
$factory->define(App\Transaction::class, function ($faker) {

    $gross = rand()*100;
    $discounts = rand()*10;

    return [
        'code' => strtoupper(str_random(10)),
        'quantity' => rand(),
        'description' => realText(10, 3),
        'gross' => $gross,
        'discounts' => $discounts,
        'net' => $gross-$discounts,
        'processed_at' => $faker->date($format = 'Y-m-d H:i:s'),
        'created_at' => $faker->date($format = 'Y-m-d H:i:s'),
        'updated_at' => $faker->date($format = 'Y-m-d H:i:s')
    ];
});
