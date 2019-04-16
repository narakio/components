<?php

use Faker\Generator as Faker;

$factory->define(Naraki\Sentry\Models\Group::class, function (Faker $faker) {
    $name = $faker->word;
    return [
        'group_name' => $name,
        'group_slug' => slugify($name),
        'group_mask' => 0x00001
    ];
});
