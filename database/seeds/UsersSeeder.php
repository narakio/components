<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $logging = DB::connection()->logging();
        DB::connection()->disableQueryLog();
        $pwd = bcrypt('secret');
        $u = factory(Naraki\Sentry\Models\User::class)->create([
            'username' => 'john_doe',
            'password' => $pwd,
            'activated' => true,
            'remember_token' => null,
        ]);
        Media::image()->createAvatar('john_doe', 'John Doe');
        factory(Naraki\Sentry\Models\Person::class)->create([
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'person_slug' => 'john-doe',
            'user_id' => $u->getAttribute('user_id')
        ]);
        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 2,
            'user_id' => $u->getAttribute('user_id')
        ]);
        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 4,
            'user_id' => $u->getAttribute('user_id')
        ]);

        $u = factory(Naraki\Sentry\Models\User::class)->create([
            'username' => 'jane_doe',
            'password' => $pwd,
            'activated' => true,
            'remember_token' => null,
        ]);

        factory(Naraki\Sentry\Models\Person::class)->create([
            'email' => 'jane.doe@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'person_slug' => 'jane-doe',
            'user_id' => $u->getAttribute('user_id')
        ]);

        Media::image()->createAvatar('jane_doe', 'Jane Doe');

        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 2,
            'user_id' => $u->getAttribute('user_id')
        ]);
        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 4,
            'user_id' => $u->getAttribute('user_id')
        ]);

        $this->makeDevAccount($pwd);

        $locales = ['fi_FI', 'fr_FR', 'en_US', 'hu_HU', 'pt_PT', 'es_ES'];
        $usernames = $slugs = [];
        $faker = Faker\Factory::create();

        for ($i = 1; $i <= 500; $i++) {
            if ($i % 20 == 0) {
                $faker = Faker\Factory::create($locales[rand(0, 5)]);
            }
            $fn = ($faker->unique()->firstName);
            $ln = ($faker->unique()->lastName);
            $ps = slugify($fn . ' ' . $ln);
            $username = substr(
                slugify(strtolower($fn . '_' . $ln), '_'),
                0,
                25
            );

            if (isset($usernames[$username])) {
                $usernames[$username]++;
            } else {
                $usernames[$username] = 0;
            }

            if (isset($slugs[$ps])) {
                $slugs[$ps]++;
            } else {
                $slugs[$ps] = 0;
            }

            $u = factory(Naraki\Sentry\Models\User::class)->create([
                'username' => ($usernames[$username] == 0) ? $username : $username . $usernames[$username],
                'activated' => true,
                'password' => $pwd,
            ]);
            factory(Naraki\Sentry\Models\Person::class)->create([
                'email' => sprintf('%s.%s@%s.%s',
                    slugify(strtolower($fn)),
                    slugify(strtolower($ln)),
                    $faker->domainWord, $faker->tld
                ),
                'first_name' => $fn,
                'last_name' => $ln,
                'person_slug' => ($slugs[$ps] == 0) ? $ps : $ps . $slugs[$ps],
                'user_id' => $u->getAttribute('user_id'),
                'created_at' => $faker->dateTimeBetween('-2 years')
            ]);

//            Media::image()->createAvatar($username, sprintf('%s %s', $fn, $ln));

            if ($i % 50 == 0) {
                $groupID = 3;
                factory(Naraki\Sentry\Models\GroupMember::class)->create([
                    "group_id" => 4,
                    'user_id' => $u->getAttribute('user_id')
                ]);
            } else {
                $groupID = 4;
            }
            factory(Naraki\Sentry\Models\GroupMember::class)->create([
                "group_id" => $groupID,
                'user_id' => $u->getAttribute('user_id')
            ]);

        }
        if ($logging) {
            DB::connection()->enableQueryLog();
        }
    }

    private function makeDevAccount($pwd)
    {
        $u = factory(Naraki\Sentry\Models\User::class)->create([
            'username' => env('MAIN_ACCOUNT_USERNAME'),
            'password' => $pwd,
            'activated' => true,
            'remember_token' => null,
        ]);

        factory(Naraki\Sentry\Models\Person::class)->create([
            'email' => env('MAIN_ACCOUNT_EMAIL'),
            'first_name' => env('MAIN_ACCOUNT_FIRST_NAME'),
            'last_name' => env('MAIN_ACCOUNT_LAST_NAME'),
            'person_slug' => slugify(
                env('MAIN_ACCOUNT_FIRST_NAME') . ' ' .
                env('MAIN_ACCOUNT_LAST_NAME'), '_'
            ),
            'user_id' => $u->getAttribute('user_id')
        ]);

        Media::image()->createAvatar(env('MAIN_ACCOUNT_USERNAME'),
            env('MAIN_ACCOUNT_FIRST_NAME') . ' ' . env('MAIN_ACCOUNT_LAST_NAME'));

        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 2,
            'user_id' => $u->getAttribute('user_id')
        ]);
        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 4,
            'user_id' => $u->getAttribute('user_id')
        ]);

    }
}
