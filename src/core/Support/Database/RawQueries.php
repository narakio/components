<?php namespace Naraki\Core\Support\Database;

use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Permission\Contracts\HasPermissions;
use Naraki\Core\Contracts\RawQueries as RawQueriesInterface;

abstract class RawQueries implements RawQueriesInterface
{
    /**
     * @param array $testedArray
     * @param $group
     * @return array
     */
    public function getUsersInArrayNotInGroup($testedArray, $group)
    {
        $userIds = \DB::select(
            sprintf('
                    SELECT user_id FROM users
                    WHERE users.username IN (%s)
                    AND user_id NOT IN (
                      SELECT users.user_id FROM users
                        JOIN group_members member ON users.user_id = member.user_id
                        JOIN groups ON member.group_id = groups.group_id
                                       AND group_name=?
                    )
                ', implode(',', array_fill(0, count($testedArray), '?')), $group
            ), array_merge($testedArray, [$group])
        );
        return array_map(function ($v) {
            return $v->user_id;
        }, $userIds);
    }

    /**
     * Returns an object containing the max permissions (root permissions are max so we grab those)
     * and user permissions.
     *
     * @param $entityTypeId
     * @return object
     */
    public function getAllUserPermissions($entityTypeId)
    {
        $results = \DB::select('
  SELECT
    permissions.entity_type_id,
    permission_mask,
    entities.entity_id as entity_id,
    "default" AS "type"
  FROM permissions
    JOIN entities ON permissions.entity_id = entities.entity_id AND permissions.entity_type_id IN (
      SELECT et_group.entity_type_id
      FROM group_members
        JOIN entity_types et_user
          ON et_user.entity_type_target_id = group_members.user_id AND et_user.entity_type_id = ?
        JOIN entity_types et_group ON et_group.entity_type_target_id = group_members.group_id
      GROUP BY group_members.group_id
    )
UNION
  SELECT
    permission_masks.permission_holder_id AS entity_type_id,
    permission_masks.permission_mask,
    entities.entity_id as entity_id,
    "computed" AS "type"
  FROM permission_masks
    JOIN permission_stores ON permission_masks.permission_store_id = permission_stores.permission_store_id 
        AND permission_masks.permission_holder_id = ? 
        AND permission_is_default = 1
    JOIN permission_records ON permission_stores.permission_store_id = permission_records.permission_store_id
    JOIN entities ON entities.entity_id = permission_records.entity_id
  GROUP BY permission_masks.permission_store_id,permission_masks.permission_mask,
  permission_masks.permission_holder_id,entities.entity_id,type
', [EntityType::ROOT_ENTITY_TYPE_ID, $entityTypeId]
        );
        $permission = [];
        foreach ($results as $result) {
            $permission[$result->type][Entity::getConstantName($result->entity_id)] =
                Entity::createModel($result->entity_id, [],
                    HasPermissions::class)->getReadablePermissions($result->permission_mask, true);
        }
        //We're supposed to get an array with computed and default permissions but some users
        // don't have permissions on anything.
        if (!isset($permission['computed'])) {
            $permission['computed'] = [];
        }
        return (object)$permission;
    }

    /**
     * @param string $name
     * @param string $primaryKey
     */
    public function triggerCreateEntityType($name, $primaryKey)
    {
        \DB::unprepared(
            sprintf('
CREATE TRIGGER t_create_entity_type_%1$s AFTER INSERT ON %1$s
FOR EACH ROW
BEGIN
INSERT into entity_types(entity_id,entity_type_target_id)
SELECT entity_id,NEW.%2$s as entity_type_target_id FROM entities WHERE entity_name="%1$s";
END
                ',
                $name, $primaryKey
            )
        );
    }

    public function triggerCreateEntityTypeUsers()
    {
        \DB::unprepared(
            '
CREATE TRIGGER t_create_entity_type_users AFTER INSERT ON users
FOR EACH ROW
BEGIN
INSERT into entity_types(entity_id,entity_type_target_id)
SELECT entity_id,NEW.user_id as entity_type_target_id FROM entities WHERE entity_name="users";
INSERT into stat_users(user_id)
VALUES(new.user_id);
END
                '
        );
    }

    /**
     * @param string $name
     * @param string $primaryKey
     * @param int $entityId
     */
    public function triggerDeleteEntityType($name, $primaryKey, $entityId)
    {
        \DB::unprepared(
            sprintf('
CREATE TRIGGER t_delete_entity_type_%1$s AFTER DELETE ON %1$s
FOR EACH ROW
BEGIN
DELETE FROM entity_types WHERE entity_type_target_id=OLD.%2$s and entity_id=%3$s;
END
                ',
                $name, $primaryKey, $entityId
            )
        );
    }

    public function triggerUserUpdateActivated()
    {
        \DB::unprepared(
    '
CREATE TRIGGER t_users_update_activated AFTER DELETE ON user_activations
FOR EACH ROW
BEGIN
update users
set activated = 1
where user_id = old.user_id;
END;
        '
        );
    }

    public function triggerUsersDelete()
    {
        \DB::unprepared('
CREATE TRIGGER t_users_delete BEFORE DELETE ON users
    FOR EACH ROW
BEGIN
    declare var_user_id INT DEFAULT 0;
    select user_id
           into var_user_id
    from users
    where user_id = old.user_id;

    update forum_posts
    set post_user_id = 0
    where post_user_id = var_user_id;
END;
        ');
    }
}