<?php namespace Naraki\Sentry\Models;

use Naraki\Core\Contracts\HasAnEntity;
use Naraki\Core\Models\Entity;
use Naraki\Core\Traits\Enumerable;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Naraki\Core\Traits\Models\HasAnEntity as HasAnEntityTrait;
use Naraki\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class GroupMember extends Model implements HasAnEntity
{
    use HasAnEntityTrait, DoesSqlStuff, Enumerable, HasPermissions;

    const PERMISSION_ADD = 0b10;
    const PERMISSION_DELETE = 0b1000;

    public static $entityID = \Naraki\Core\Models\Entity::GROUP_MEMBERS;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'group_id'
    ];

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeGroup(Builder $builder)
    {
        return $this->joinWithKey($builder, Group::class, 'group_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeUser(Builder $builder)
    {
        return $this->joinWithKey($builder, User::class, 'user_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder)
    {
        return $builder->join('entity_types', function (JoinClause $q) {
            $q->on('entity_types.entity_type_target_id', '=', 'group_members.user_id')
                ->where('entity_types.entity_id', '=', Entity::USERS);
        });
    }

}
