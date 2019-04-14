<?php namespace Naraki\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Naraki\Sentry\Models\User;

class EntityType extends Model
{
    public $timestamps = false;
    public static $entityColumn = 'entity_types.entity_type_target_id';
    public static $simpleEntityColumn = 'entity_type_target_id';
    protected $primaryKey = 'entity_type_id';
    protected $fillable = ['entity_id', 'entity_type_target_id', 'entity_type_id'];
    //We hard code database ids here so we don't have to make the query. Those IDs will never change so we can do this.
    const ROOT_ENTITY_TYPE_ID = 2;
    const ROOT_GROUP_ENTITY_TYPE_ID = 4;

    /**
     * Gets the entity type ID with an entity ID
     *
     * Example: getEntityTypeID(15,5)
     * returns user entity type id for user ID 5 (15 is the user entity ID)
     * ID 5 could belong to a group, forum post, etc., so we specify the entityId
     * so the query can start from the user table
     *
     * @see \Naraki\Core\Models\Entity
     * @param $entityID
     * @param int|array|\stdClass $filter
     *
     * @return array|int
     */
    public static function getEntityTypeID($entityID, $filter)
    {
        $baseBuilder = static::getEntityInfo($entityID, $filter);
        if (is_array($filter)) {
            return $baseBuilder->pluck('entity_type_id')->all();
        }
        return $baseBuilder->pluck('entity_type_id')->first();
    }

    /**
     * Gets the entity type ID with an entity ID
     *
     * Example: getEntityTypeID(15,5)
     * returns all the user entity type info for user ID 5 (15 is the user entity ID)
     *
     * @see \Naraki\Core\Models\Entity
     *
     * @param int $entityID
     * @param int|array $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getEntityInfo($entityID, $filter)
    {
        $baseBuilder = static::query();
        /**
         * @var \Illuminate\Database\Eloquent\Builder $builderWithEntity
         */
        $builderWithEntity = call_user_func([$baseBuilder, 'entityType'], $entityID)
            ->where('entity_id', $entityID);

        if (is_int($filter)) {
            $builderWithEntity->where('entity_type_target_id', $filter);
        } elseif (is_array($filter)) {
            $builderWithEntity->whereIn('entity_type_target_id', $filter);
        } else {
            $builderWithEntity->where(static::getEntitySlugColumn($entityID), '=', $filter);
        }

        return $builderWithEntity;
    }

    /**
     * @param $entityID
     * @param $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getEntityInfoFromSlug($entityID, $filter)
    {
        $class = Entity::getModelClassNamespace($entityID);
        $baseBuilder = new $class();

        /**
         * @var \Illuminate\Database\Eloquent\Model $baseBuilder
         * @var \Illuminate\Database\Eloquent\Builder $builderWithEntity
         */
        $builderWithEntity = $baseBuilder->newQuery()
            ->join(
                'entity_types',
                $baseBuilder->getQualifiedKeyName(), '=',
                'entity_types.entity_type_target_id'
            )->where('entity_types.entity_id', $entityID);

        if (is_array($filter)) {
            $builderWithEntity->whereIn(static::getEntitySlugColumn($entityID), $filter);
        } else {
            $builderWithEntity->where(static::getEntitySlugColumn($entityID), '=', $filter);
        }
        return $builderWithEntity;
    }

    /**
     * @param $entityID
     * @param $entityTypeID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function buildQueryFromEntity($entityID, $entityTypeID = null): Builder
    {
        $class = Entity::getModelClassNamespace($entityID);
        $baseBuilder = new $class();

        /**
         * @var \Illuminate\Database\Eloquent\Model $baseBuilder
         * @var \Illuminate\Database\Eloquent\Builder $builderWithEntity
         */
        $builderWithEntity = $baseBuilder->newQuery()
            ->join(
                'entity_types',
                $baseBuilder->getQualifiedKeyName(), '=',
                'entity_types.entity_type_target_id'
            )->where('entity_types.entity_id', $entityID);
        if (!is_null($entityTypeID)) {
            if (is_array($entityTypeID)) {
                $builderWithEntity->whereIn('entity_type_id', $entityTypeID);
            } else {
                $builderWithEntity->where('entity_type_id', $entityTypeID);
            }
        }
        return $builderWithEntity;
    }

    /**
     * For when we have an entity type ID and we want to build a query from the entity
     * but don't know what kind of entity it is.
     * Example: I know this media is attached to something and I want to get this something's slug
     * so I can generate a link to it. I need to make a query like 'find the entity this media is attached to
     * and give me its slug'
     *
     * @param int $entityTypeID
     * @return mixed|null
     * @see \Naraki\Core\Models\EntityType
     */
    public static function buildQueryFromUnknownEntity($entityTypeID)
    {
        $et = new self();
        $entityId = $et->newQuery()->select('entity_id')
            ->where($et->getKeyName(), $entityTypeID)->get()->pluck('entity_id')->first();
        if (!is_null($entityId)) {
            return (object)['query' => self::buildQueryFromEntity($entityId, $entityTypeID), 'entity' => $entityId];
        } else {
            return null;
        }
    }

    /**
     * @param $entityID
     * @param $targetID
     * @return mixed
     */
    public static function buildQueryFromTarget($entityID, $targetID)
    {
        $class = Entity::getModelClassNamespace($entityID);
        $baseBuilder = new $class();

        /**
         * @var \Illuminate\Database\Eloquent\Model $baseBuilder
         * @var \Illuminate\Database\Eloquent\Builder $builderWithEntity
         */
        $builderWithEntity = $baseBuilder->newQuery()
            ->join(
                'entity_types',
                $baseBuilder->getQualifiedKeyName(), '=',
                'entity_types.entity_type_target_id'
            )->where('entity_types.entity_id', $entityID);
        if (is_array($targetID)) {
            $builderWithEntity->whereIn('entity_type_target_id', $targetID);
        } else {
            $builderWithEntity->where('entity_type_target_id', $targetID);
        }

        return $builderWithEntity;
    }

    /**
     * Returns the name of the column the entity uses to store names,
     * the most common being "name", but it can be something else like "title"
     *
     * @param $entityID
     *
     * @return string
     */
    public static function getEntitySlugColumn($entityID)
    {
        /**
         * @var \Naraki\Core\Traits\Models\HasASlug $targetType
         */
        $targetType = Entity::getModelClassNamespace($entityID);

        return $targetType::$slugColumn;
    }

    /**
     * @param int $entityID
     * @param int $targetID
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getByTargetID($entityID, $targetID)
    {
        return static::query()
            ->where('entity_type_target_id', $targetID)
            ->where('entity_id', $entityID);
    }

    /**
     * Makes the join on this table for any entity
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $entityID
     *
     * @return mixed
     */
    public function scopeEntityType(Builder $query, $entityID)
    {
        $class = Entity::getModelClassNamespace($entityID);
        /**
         * @var \Illuminate\Database\Eloquent\Model $instance
         */
        $instance = new $class();

        $primaryKey = $instance->getQualifiedKeyName();

        return $query->join(
            Entity::getConstantName($entityID),
            $primaryKey, '=', 'entity_types.entity_type_target_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $entityId
     * @param array $userIdList
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeHighestGroup(Builder $builder, $entityId, $userIdList)
    {
        return $builder->joinSub(User::queryHighestRankedGroup($userIdList),
            'users_highest_group',
            'entity_types.entity_type_target_id',
            '=',
            'users_highest_group.user_id')
            ->where('entity_types.entity_id', '=', $entityId);
    }
}
