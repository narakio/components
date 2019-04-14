<?php namespace Naraki\Permission\Support;

use Naraki\Permission\Models\PermissionMask;
use Naraki\Permission\Models\PermissionRecord;
use Naraki\Permission\Models\PermissionStore;

class Group extends Permission
{
    public function assignPermissions()
    {
        $this->populateDb($this->getUsersWithPermissions());
    }

    /**
     * @param \stdClass[] $usersWithPermissions
     */
    protected function populateDb($usersWithPermissions)
    {
        $groups = $this->sqlGetGroups();
        $users = $this->sqlGetUsersAndHighestGroup(array_keys($usersWithPermissions));
        $userInfo = [];
        foreach ($users as $user) {
            $userInfo[$user->user_id] = (object)$user->toArray();
        }
        unset($users);

        $permissionMasks = $permissionRecords = [];

        //The default permissions will be used whenever the user tries to view or edit groups in general
        foreach ($usersWithPermissions as $userWithPermission) {
            $permissionStoreId = (new PermissionStore())->insertGetId([]);
            $permissionMasks[] = [
                'permission_store_id' => $permissionStoreId,
                'permission_holder_id' => $userInfo[$userWithPermission->user_id]->entity_type_id,
                'permission_mask' => $userWithPermission->permission_mask,
                'permission_is_default' => true
            ];
            $permissionRecords[] = [
                'entity_id' => $this->entityId,
                'permission_target_id' => 0,
                'permission_store_id' => $permissionStoreId
            ];
        }
        //For groups there is the specific rule that you're not supposed to see groups above one's "pay grade"
        //that is, groups that have a lower group mask than the higher group one belongs to
        foreach ($groups as $group) {
            foreach ($usersWithPermissions as $userWithPermission) {
                if ($userInfo[$userWithPermission->user_id]->group_mask < $group->getAttribute('group_mask')) {
                    $permissionStoreId = (new PermissionStore())->insertGetId([]);
                    $permissionMasks[] = [
                        'permission_store_id' => $permissionStoreId,
                        'permission_holder_id' => $userInfo[$userWithPermission->user_id]->entity_type_id,
                        'permission_mask' => $userWithPermission->permission_mask,
                        'permission_is_default' => false
                    ];
                    $permissionRecords[] = [
                        'entity_id' => $this->entityId,
                        'permission_target_id' => $group->getAttribute('entity_type_id'),
                        'permission_store_id' => $permissionStoreId
                    ];
                }
            }
        }
        (new PermissionRecord)->insert($permissionRecords);
        (new PermissionMask)->insert($permissionMasks);
    }

    private function sqlGetGroups()
    {
        return \Naraki\Sentry\Models\Group::query()->select(['entity_type_id', 'group_mask'])->entityType()->get();
    }

}