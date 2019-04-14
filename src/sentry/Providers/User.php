<?php namespace Naraki\Sentry\Providers;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Str;
use Naraki\Core\EloquentProvider;
use Naraki\Core\Models\Entity;
use Naraki\Core\Traits\Filterable;
use Naraki\Media\Facades\Media as MediaProvider;
use Naraki\Media\Models\Media;
use Naraki\Media\Models\MediaGroupType;
use Naraki\Media\Models\MediaType;
use Naraki\Oauth\Contracts\OauthProcessable as OauthProcessableInterface;
use Naraki\Oauth\Traits\OauthProcessable;
use Naraki\Sentry\Contracts\Person as PersonInterface;
use Naraki\Sentry\Contracts\User as UserInterface;
use Naraki\Sentry\Models\StatUser;
use Naraki\Sentry\Models\User as UserModel;
use Naraki\Sentry\Models\UserActivation;
use Tymon\JWTAuth\JWTGuard;

/**
 * Class User
 * @see \Illuminate\Auth\EloquentUserProvider
 * @method \Naraki\Sentry\Models\User createModel(array $attributes = [])
 * @method \Naraki\Sentry\Models\User getOne($id, $columns = ['*'])
 */
class User extends EloquentProvider implements UserProvider, UserInterface, OauthProcessableInterface
{
    use OauthProcessable, DispatchesJobs, Filterable;

    /**
     * @var string
     */
    protected $model = \Naraki\Sentry\Models\User::class;
    /**
     * @var string
     */
    protected $filter = \Naraki\Sentry\Models\Filters\User::class;
    /**
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;
    /**
     * @var \Naraki\Sentry\Contracts\Person|\Naraki\Sentry\Providers\Person
     */
    private $person;

    /**
     *
     * @param \Naraki\Sentry\Contracts\Person|\Naraki\Sentry\Providers\Person $p
     */
    public function __construct(PersonInterface $p)
    {
        parent::__construct();
        $this->hasher = new BcryptHasher();
        $this->person = $p;
    }

    /**
     * @return \Naraki\Sentry\Contracts\Person|\Naraki\Sentry\Providers\Person
     */
    public function person()
    {
        return $this->person;

    }

    /**
     * @param $data
     * @param bool $activate
     * @return \Naraki\Sentry\Models\User
     */
    public function createOne($data, $activate = false)
    {
        //Looking for a person without user_id so we can reuse the email
        $existingPerson = $this->person
            ->select(['user_id', 'person_id'])
            ->where('email', $data['email'])
            ->where('user_id', 0)->first();
        $user = $this->createModel([
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
            'activated' => $activate
        ]);
        $user->save();
        $person = $this->person->createModel();
        $fillables = $this->person->filterFillables($data, $person);
        $fillables['user_id'] = $user->getKey();
        if (!is_null($existingPerson)) {
            $existingPerson->fill($fillables)->save();
        } else {
            $person->fill($fillables)->save();
        }

        return $user->newQuery()->whereKey($user->getKey())->scopes(['entityType'])->first();
    }


    /**
     * @param \Naraki\Sentry\Models\User $user
     * @return string
     */
    public function generateActivationToken($user)
    {
        try {
            $token = makeHexHashedUuid();
        } catch (\Exception $e) {
        }
        (new UserActivation([
            'user_id' => $user->getKey(),
            'activation_token' => $token
        ]))->save();
        return $token;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $data
     * @return \Naraki\Sentry\Models\User
     */
    public function updateOne($field, $value, $data)
    {
        $model = $this->createModel();
        $user = $model->newQuery()->where($field, $value)->scopes(['entityType'])->first();

        $this->person->build()
            ->where('person_id', $user->person_id)
            ->update($this->person->filterFillables($data));

        $filteredData = $this->filterFillables($data);
        if (!empty($filteredData)) {
            $model->newQueryWithoutScopes()->where($field, $value)
                ->update($filteredData);
        }

        if (isset($data[$field])) {
            return $model->newQuery()->where($field, $data[$field])
                ->scopes(['entityType'])->first();
        } else {
            return $model->newQuery()->where($field, $value)->scopes(['entityType'])->first();
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return \Naraki\Sentry\Models\User
     */
    public function updateOneById($id, $data)
    {
        return $this->updateOne($this->createModel()->getKeyName(), $id, $data);
    }

    /**
     * @param string $username
     * @param array $data
     * @return \Naraki\Sentry\Models\User
     */
    public function updateOneByUsername($username, $data)
    {
        return $this->updateOne('username', $username, $data);
    }

    /**
     * We make sure to delete physical images first, because we have to grab image information that won't be present
     * if we delete users first. The user deletion deletes any entities with a foreign constraint on it, so media
     * db entries would be deleted and we'd lose the media info we need to fetch the right image for deletion.
     *
     * @param string|array $username
     * @throws \Exception
     */
    public function deleteByUsername($username)
    {
        $db = $this->select(['person_id', 'users.user_id', 'entity_types.entity_type_id'])
            ->scopes(['entityType']);
        if (is_array($username)) {
            $dbResult = $db->whereIn('username', $username['users'])->get();
            $people = $dbResult->pluck('person_id');
            $users = $dbResult->pluck('user_id');
            $entityType = $dbResult->pluck('entity_type_id')->toArray();
            MediaProvider::image()->deleteByEntity($entityType, Entity::USERS, Media::IMAGE_AVATAR);
            $this->person()->select(['person_id'])
                ->whereIn('person_id', $people)
                ->delete();
            $this->createModel()->newQueryWithoutScopes()->select(['user_id'])
                ->whereIn('user_id', $users)
                ->delete();

        } else {
            $dbResult = $db->where('username', '=', $username)
                ->first();
            $person = $dbResult->getAttribute('person_id');
            $user = $dbResult->getAttribute('user_id');
            $entityType = $dbResult->getAttribute('entity_type_id');
            MediaProvider::image()->deleteByEntity($entityType, Entity::USERS, Media::IMAGE_AVATAR);
            $this->person()->select(['person_id'])
                ->whereKey($person)
                ->delete();
            $this->createModel()->newQueryWithoutScopes()->select(['users.user_id'])
                ->whereKey($user)
                ->delete();
        }

    }

    /**
     * @param string $username
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildOneByUsername($username, $columns = ['*'])
    {
        return $this
            ->select($columns)->where('username', $username);
    }


    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        $builder = $model->newQuery()->scopes(['entityType'])
            ->where($model->getIdentifier($identifier), $identifier);

        return $builder->first();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed $identifier
     * @param string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        $model = $model->newQuery()->scopes(['entityType'])
            ->where($model->getIdentifier(), $identifier)->first();

        if (!$model) {
            return null;
        }

        $rememberToken = $model->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $model : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $user->setRememberToken($token);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        $builder = $this->createModel()->newQuery()->scopes(['entityType']);

        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $builder->where($key, $value)->where('activated', '=', true);
            }
        }

        return $builder->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * @param string $search
     * @param int $userEntityId
     * @param int $limit
     * @return mixed
     */
    public function search($search, $userEntityId, $limit)
    {
        return $this
            ->buildWithScopes(
                ['full_name as text', 'username as id'],
                ['entityType', 'permissionRecord', 'permissionStore', 'permissionMask' => $userEntityId])
            ->where('full_name', 'like', sprintf('%%%s%%', $search))
            ->limit($limit);
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAvatars($userId)
    {
        return $this->buildWithScopes(
            ['media_uuid as uuid', 'media_extension as ext', 'media_in_use as used'],
            ['entityType' => $userId, 'avatars' => false])
            ->get()->toArray();
    }


    /**
     * @param string $token
     * @return int
     */
    public function activate($token)
    {
        $model = new UserActivation;
        $user = $model->newQuery()
            ->select(['user_id'])
            ->where('activation_token', '=', $token)
            ->first();
        if (!is_null($user)) {
            $userId = $user->value('user_id');
            return $model->newQuery()->where('user_id', '=', $userId)->delete();
            //A mysql trigger sets the activated boolean on the users table whenever a delete occurs.
        }
        return 0;
    }

    /**
     * @param \Naraki\Sentry\Models\User $user
     * @param array $values
     */
    public function updateStats(UserModel $user, $values)
    {
        if (isset($values['stat_user_timezone']) && is_numeric($values['stat_user_timezone'])) {
            StatUser::query()->where('user_id', $user->getKey())
                ->update(['stat_user_timezone' => intval($values['stat_user_timezone'])]);
        }
    }

    /**
     * Gets the hasher implementation.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->createModel()->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}