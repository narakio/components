<?php namespace Naraki\Oauth\Traits;

use Naraki\Sentry\Models\User as UserModel;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Naraki\Media\Jobs\ProcessAvatar;
use Naraki\Oauth\Models\OAuthProvider;
use Naraki\Oauth\Socialite\GoogleUser;

/**
 * @see \Naraki\Sentry\Providers\User
 * @method \Naraki\Sentry\Models\User createModel(array $attributes = [])
 * @property \Naraki\Sentry\Providers\Person $person
 */
trait OauthProcessable
{
    /**
     * @param string $provider
     * @param \Naraki\Oauth\Socialite\GoogleUser $socialiteUser
     * @return \Naraki\Sentry\Models\User
     */
    public function processViaYolo(string $provider, GoogleUser $socialiteUser): UserModel
    {
        $model = $this->createModel();

        /**
         * @var \Illuminate\Database\Eloquent\Builder $user
         */
        $user = $model->newQuery()
            ->select(['users.*', 'people.*', 'entity_type_id'])
            ->scopes(['entityType'])
            ->oauth([
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getAttribute('subject')
            ]);

        if (!$user->exists()) {
            $user = $model->newQuery()
                ->select(['users.*', 'entity_type_id'])
                ->scopes(['entityType'])
                ->where('email', $socialiteUser->getAttribute('email'))->first();
        } else {
            return $user->first();
        }

        if (is_null($user)) {
            $username = trim(substr(
                slugify($socialiteUser->getAttribute('fullName'), '_'),
                0,
                15
            ), '_');

            if (is_null($username) || empty($username)) {
                $tmp = explode('@', $socialiteUser->getAttribute('email'), 1);
                $username = substr(slugify($tmp[0], '_'), 0, 15);
            }
            $username = $this->parseUsername($model, $username);;
            $user = $this->createModel([
                'username' => $username,
                'activated' => true
            ]);
            $user->save();
            $this->person->createModel([
                'user_id' => $user->getKey(),
                'email' => $socialiteUser->getAttribute('email'),
                'first_name' => $socialiteUser->getAttribute('firstName'),
                'last_name' => $socialiteUser->getAttribute('lastName'),
                'full_name' => $socialiteUser->getAttribute('fullName')
            ])->save();
            $avatarUrl = $socialiteUser->getAttribute('avatar');

            $this->dispatch(new ProcessAvatar($avatarUrl, $username));
            $user = $model->newQuery()
                ->select(['users.*', 'people.*', 'entity_type_id'])
                ->scopes(['entityType'])
                ->whereKey($user->getKey())->first();
        }

        OAuthProvider::query()->create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_user_id' => $socialiteUser->getAttribute('subject')
        ]);

        return $user;

    }

    /**
     * @param string $provider
     * @param \Laravel\Socialite\Contracts\User $socialiteUser
     * @return \Naraki\Sentry\Models\User
     */
    public function processViaOAuth(string $provider, SocialiteUser $socialiteUser): UserModel
    {
        $model = $this->createModel();
        /**
         * @var \Illuminate\Database\Eloquent\Builder $user
         */
        $user = $model->newQuery()
            ->select(['oauth_provider_id', 'users.*', 'people.*', 'entity_type_id'])
            ->scopes(['entityType'])
            ->oauth([
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getId()
            ]);

        if ($user->exists()) {
            $user = $user->first();
            OAuthProvider::query()
                ->where('oauth_provider_id', $user->getAttribute('oauth_provider_id'))
                ->update([
                    'access_token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken ?? null,
                ]);
            return $user;
        } else {
            $user = $model->newQuery()
                ->select(['users.*', 'people.*', 'entity_type_id'])
                ->scopes(['entityType'])
                ->where('email', $socialiteUser->getEmail())->first();
        }

        if (is_null($user)) {
            $nickname = $socialiteUser->getNickname();
            if (is_null($nickname)) {
                $nickname = $socialiteUser->getName();
            }
            $username = substr(slugify($nickname, '_'), 0, 15);

            $username = $this->parseUsername($model, $username);
            $user = $this->createModel([
                'username' => $username,
                'activated' => true
            ]);
            $user->save();
            $this->person->createModel([
                    'user_id' => $user->getKey(),
                    'email' => $socialiteUser->getEmail(),
                    'full_name' => $socialiteUser->getName()
                ]
            )->save();
            $avatarUrl = $socialiteUser->getAvatar();
            $this->dispatch(new ProcessAvatar($avatarUrl, $username));
            $user = $model->newQuery()
                ->select(['users.*', 'people.*', 'entity_type_id'])
                ->scopes(['entityType'])
                ->whereKey($user->getKey())->first();
        }

        OAuthProvider::query()->create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_user_id' => $socialiteUser->getId(),
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
        ]);

        return $user;

    }

    private function parseUsername($model, $username)
    {
        if ($model->newQueryWithoutScopes()->select(['username'])
            ->where('username', $username)->exists()) {
            $latestUsername =
                $model->newQueryWithoutScopes()->select(['username'])
                    ->whereRaw(sprintf(
                            'username = "%s" or username LIKE "%s-%%"',
                            $username,
                            $username)
                    )
                    ->latest($model->getKeyName())
                    ->value('username');
            if ($latestUsername) {
                $pieces = explode('-', $latestUsername);

                $number = intval(end($pieces));

                $suffix = sprintf('-%s', ($number + 1));
                $username = substr($username, 0, -(strlen($suffix))) . $suffix;
            }
        }
        return $username;
    }


}