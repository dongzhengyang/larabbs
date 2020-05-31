<?php
use Faker\Generator as Faker;
use App\Models\UserData;
use Illuminate\Support\Str;

$factory->define(UserData::class, function (Faker $faker) {
   $date_time = $faker->date . ' ' . $faker->time;
    return [
        'mileage' => $faker->randomFloat(2, 10, 10000),
        //'created_at' => $date_time,
        //'updated_at' => $date_time,
    ];
});
