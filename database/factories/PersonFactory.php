<?php

use Faker\Generator as Faker;

$factory->define(Naraki\Sentry\Models\Person::class, function (Faker $faker) {
    $fn = $faker->firstName;
    $ln = $faker->lastName;

    return [
        'email' => $faker->unique()->email,
        'first_name' => $fn,
        'last_name' => $ln
    ];
});
