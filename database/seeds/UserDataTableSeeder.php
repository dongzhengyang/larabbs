<?php

use Illuminate\Database\Seeder;
use App\Models\UserData;

class UserDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = app(Faker\Generator::class);
        
        $userdatas = factory(UserData::class)->times(20000)->make()
                ->each(function ($userdata) use ($faker) {
                });

        UserData::insert($userdatas->toArray());
    }
}
