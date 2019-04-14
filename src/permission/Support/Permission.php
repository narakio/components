<?php namespace Naraki\Permission\Support;

use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Sentry\Models\GroupMember;

abstract class Permission
{
    /**
     * @var int
     */
    protected $fullPermissionsBitmask;
    /**
     * @var array
     */
    protected $allUsersInfo;
    /**
     * @var PermissionStoreData
     */
    protected $default;
    /**
     * @var PermissionStoreData
     */
    protected $computed;
    /**
     * @var int
     */
    protected $entityId;
    /**
     * @var array
     */
    private static $permissionBoundEntities = [
        Entity::USERS,
        Entity::GROUPS,
        Entity::BLOG_POSTS,
        Entity::MEDIA,
        Entity::SYSTEM
    ];

    protected function __construct($entityId)
    {
        $modelClass = Entity::getModelClassNamespace($entityId);
        $this->fullPermissionsBitmask = array_sum(
            forward_static_call([$modelClass, 'getConstants'], 'PERMISSION')
        );
        $this->entityId = $entityId;
    }

    /**
     * When we assign permissions, either the entity (i.e. users, groups, etc.) has a specific class
     * within this namespace to handle their permissions, or, if there's nothing specific about permissions
     * for this entity we use a generic class.
     * For example, users have a specific class because we have to know which users have permissions over others,
     * same thing for groups. For blog posts however, permissions are more straightforward, there are no specifics.
     */
    public static function assignToAll()
    {
        foreach (self::$permissionBoundEntities as $entity) {
            $class = sprintf('%s\\%s', __NAMESPACE__, Entity::getModelClass($entity));
            if (class_exists($class)) {
                (new $class($entity))->assignPermissions();
            } else {
                (new Generic($entity))->assignPermissions();
            }
        }
    }

    /**
     * Arranging users in an array of type:
     *      array[user_id]=user permission
     *
     * Note: entries are ordered so that users with individually assigned permissions appear first,
     * and users with group related permissions appear next.
     * That way, we make sure that user permissions take priority over group permissions.
     *
     * @return array
     */
    protected function getUsersWithPermissions()
    {
        $dbEntries = $this->sqlUsersWithPermissions();
        $usersWithPermissions = $alreadyHasIndividualPermissions = $maxGroupPermission = [];
        foreach ($dbEntries as $dbEntry) {
            //Query does two left joins, so users without groups appear with a null group.
            //This "if" is for users who have individual permissions assigned to them.
            //Individual permissions bypass group permissions.
            if (!is_null($dbEntry->group_user_id) && !isset($alreadyHasIndividualPermissions[$dbEntry->group_user_id])) {
                $dbEntry->user_id = $dbEntry->group_user_id;
                //If a user is a member of multiple groups with different permission masks, we add permissions
                if (isset($usersWithPermissions[$dbEntry->group_user_id])) {
                    $usersWithPermissions[$dbEntry->group_user_id]->permission_mask =
                        (
                            $usersWithPermissions[$dbEntry->group_user_id]->permission_mask
                            |
                            $dbEntry->permission_mask
                        );
                } else {
                    $usersWithPermissions[$dbEntry->group_user_id] = (object)$dbEntry->toArray();
                    unset($usersWithPermissions[$dbEntry->group_user_id]->group_user_id);
                }
            } elseif (!is_null($dbEntry->user_id)) {
                $alreadyHasIndividualPermissions[$dbEntry->user_id] = (object)$dbEntry->toArray();
                $usersWithPermissions[$dbEntry->user_id] = (object)$dbEntry->toArray();
                unset($usersWithPermissions[$dbEntry->user_id]->group_user_id);
            }
        }
        return $usersWithPermissions;
    }

    /**
     * Pulling users from the database who have permissions, either individually assigned or through groups.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sqlUsersWithPermissions()
    {
        return \Naraki\Permission\Models\Permission::query()->select([
            'group_members.user_id as group_user_id',
            'users.user_id',
            'permission_mask'
        ])->entity($this->entityId)
            ->entityType()
            ->leftGroupMember()
            ->leftUser()
            ->orderBy('group_user_id')
            ->orderBy('users.user_id')
            ->get();
    }

    /**
     * SQL query that returns which users belong to which group. Allows us to determine which of the users' groups
     * is highest in the hierarchy. The hierarchy is determined using the group_mask column, where
     * higher status groups are given the lowest mask.
     *
     * @param array $userIdList
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function sqlGetUsersGroups($userIdList = null)
    {
        $builder = GroupMember::query()->select([
            'entity_types.entity_type_id',
            'users.user_id',
        ])->group()->user()->entityType();
        if (!is_null($userIdList)) {
            $builder->whereIn('users.user_id', $userIdList);
        }
        return $builder->get();
    }

    /**
     * @param null|array $userIdList
     * @return mixed
     * @see \Naraki\Core\Models\EntityType::scopeHighestGroup()
     * @see \Naraki\Sentry\Models\User::queryHighestRankedGroup
     */
    protected function sqlGetUsersAndHighestGroup($userIdList = null)
    {
        $builder = EntityType::query()->select([
            'entity_types.entity_type_id',
            'users_highest_group.user_id',
            'users_highest_group.gmask as group_mask'
        ])->highestGroup(Entity::USERS, $userIdList);
        return $builder->get();
    }

}