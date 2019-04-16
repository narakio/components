<?php

use Faker\Generator as Faker;

$factory->define(Naraki\Sentry\Models\GroupMember::class, function (Faker $faker) {
    return [
        'group_id' => 1,
        'user_id' => 1
    ];
});
