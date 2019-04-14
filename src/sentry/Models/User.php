<?php namespace Naraki\Sentry\Models;

use Carbon\Carbon;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as LaravelUser;
use Illuminate\Notifications\Notifiable;
use Naraki\Core\Contracts\Enumerable as EnumerableContract;
use Naraki\Core\Contracts\HasAnEntity as HasAnEntityContract;
use Naraki\Core\Models\Entity;
use Naraki\Core\Traits\Enumerable;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Naraki\Core\Traits\Models\HasAnEntity as HasAnEntity;
use Naraki\Core\Traits\Models\HasASlug;
use Naraki\Core\Traits\Models\Presentable;
use Naraki\Mail\Emails\User\PasswordReset;
use Naraki\Mail\Jobs\SendMail;
use Naraki\Media\Models\MediaEntity;
use Naraki\Oauth\Contracts\HasOauthScope as HasOauthScopeInterface;
use Naraki\Oauth\Traits\HasOauthScope;
use Naraki\Permission\Contracts\HasPermissions as HasPermissionsContract;
use Naraki\Permission\Traits\HasPermissions as HasPermissions;
use Naraki\System\Facades\System;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends LaravelUser implements JWTSubject, HasAnEntityContract, HasPermissionsContract, EnumerableContract, HasOauthScopeInterface
{
    use Notifiable, HasAnEntity, HasASlug, DoesSqlStuff, Enumerable, Presentable, HasPermissions, HasOauthScope;

    const PERMISSION_VIEW = 0b1;
    const PERMISSION_ADD = 0b10;
    const PERMISSION_EDIT = 0b100;
    const PERMISSION_DELETE = 0b1000;

    public $table = 'users';
    protected $primaryKey = 'user_id';
    public static $slugColumn = 'username';
    protected $fillable = [
        'username',
        'password',
        'activated',
        'remember_token'
    ];
    protected $hidden = [
        'password',
        'remember_token',
        'activated',
        'updated_at',
        'person_id'
    ];
    protected $sortable = [
        'full_name',
        'email',
        'created_ago'
    ];

    public static $entityID = \Naraki\Core\Models\Entity::USERS;
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('person', function ($builder) {
            (new static)->scopePerson($builder);
        });
    }

    public function getCreatedAgoAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
    }

    /**
     * @return int
     */
    public function getEntityType()
    {
        return $this->getAttribute('entity_type_id');

    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getAttribute('email');
    }

    public function getIdentifier($identifier = null)
    {
        if (is_int($identifier)) {
            return 'users.user_id';
        }
        return 'people.email';
    }

    public function getFullname()
    {
        return $this->getAttribute('full_name');
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->getAttribute('email');
    }

    /**
     * @param int $systemEventID
     * @return bool
     */
    public function shouldBeNotified(int $systemEventID): bool
    {
        $notifs = System::subscriptions()
            ->cacheLiveNotifications(auth()->user()->getKey());

        if (is_array($notifs) && !empty($notifs)) {
            $notifKeys = array_flip($notifs);
            return isset($notifKeys[$systemEventID]);
        }
        return false;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        app(Dispatcher::class)
            ->dispatch(
                new SendMail(
                    new PasswordReset(
                        [
                            'user' => $this,
                            'token' => $token
                        ])
                )
            );

    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     * @link https://laravel.com/docs/eloquent#local-scopes
     *
     */
    public function scopeActivated(Builder $query)
    {
        return $query->where('activated', '=', 1);

    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePerson(Builder $query)
    {
        return $this->join($query, Person::class);
    }

    public static function scopeGenericPerson(Builder $builder)
    {
        return $builder->join('people',
            'people.user_id',
            '=',
            'users.user_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder, $userId = null)
    {
        return $builder->join('entity_types', function (JoinClause $q) use ($userId) {
            $q->on('entity_types.entity_type_target_id', '=', 'users.user_id')
                ->where('entity_types.entity_id', '=', Entity::USERS);
            if (!is_null($userId)) {
                $q->where('entity_types.entity_type_target_id',
                    '=', $userId);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopePermissionRecord(Builder $builder, $entityId = Entity::USERS)
    {
        return $builder->join('permission_records', function (JoinClause $q) use ($entityId) {
            $q->on('permission_records.permission_target_id', '=', 'entity_types.entity_type_id')
                ->where('permission_records.entity_id', '=', $entityId);
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopePermissionStore(Builder $builder)
    {
        return $builder->join('permission_stores',
            'permission_stores.permission_store_id',
            '=',
            'permission_records.permission_store_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopePermissionMask(Builder $builder, $userId)
    {
        return $builder->join('permission_masks', function (JoinClause $q) use ($userId) {
            $q->on(
                'permission_masks.permission_store_id',
                '=',
                'permission_records.permission_store_id')
                ->where('permission_masks.permission_holder_id', '=', $userId);
//                ->where('permission_mask', '>', 0);
        });
    }

    public static function queryHighestRankedGroup($userIdList = null)
    {
        $query = (new static)->newBaseQueryBuilder()
            ->select(['users.user_id', \DB::raw('min(groups.group_mask) as gmask')])
            ->from('users')
            ->join('group_members', 'group_members.user_id', '=', 'users.user_id')
            ->join('groups', 'group_members.group_id', '=', 'groups.group_id')
            ->groupBy('users.user_id');
        if (!is_null($userIdList)) {
            $query->whereIn('users.user_id', $userIdList);
        }
        return $query;
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $groupName
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeGroupMember(Builder $builder, $groupName = null)
    {
        return $builder->join('group_members',
            'group_members.user_id',
            '=',
            'users.user_id'
        )->join('groups', function (JoinClause $q) use ($groupName) {
            $q->on('group_members.group_id', '=', 'groups.group_id');
            if (!is_null($groupName)) {
                $q->where('group_name', '=', $groupName);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeActivation(Builder $builder)
    {
        return $this->join($builder, UserActivation::class);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeSubscriptions(Builder $builder)
    {
        return $builder->leftJoin('system_user_subscriptions',
            'system_user_subscriptions.user_id',
            '=',
            'users.user_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param bool $inUse
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeAvatars(Builder $builder, $inUse = true)
    {
        return MediaEntity::scopeImage($builder, $inUse);
    }

}
