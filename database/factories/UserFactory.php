<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Naraki\Sentry\Models\User::class, function (Faker $faker) {
    return [
        'username' => str_replace('.','_',$faker->userName),
        'password' => '$2y$10$/hSd.IkT1eE22XjryeAfhOKozVXkYGBPldg4OzeLuFcsE813JNAAO',
        'activated'=>true,
    ];
});
