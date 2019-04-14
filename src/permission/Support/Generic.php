<?php namespace Naraki\Permission\Support;

use Naraki\Permission\Models\PermissionMask;
use Naraki\Permission\Models\PermissionRecord;
use Naraki\Permission\Models\PermissionStore;

class Generic extends Permission
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
        $users = $this->sqlGetUsersGroups(array_keys($usersWithPermissions));
        $userInfo = [];
        foreach ($users as $user) {
            $userInfo[$user->user_id] = $user->getAttribute('entity_type_id');
        }
        unset($users);
        $permissionMasks = $permissionRecords = [];
        foreach ($usersWithPermissions as $userWithPermission) {
            $permissionStoreId = (new PermissionStore())->insertGetId([]);
            $permissionMasks[] = [
                'permission_store_id' => $permissionStoreId,
                'permission_holder_id' => $userInfo[$userWithPermission->user_id],
                'permission_mask' => $userWithPermission->permission_mask,
                'permission_is_default' => true
            ];
            $permissionRecords[] = [
                'entity_id' => $this->entityId,
                'permission_target_id' => 0,
                'permission_store_id' => $permissionStoreId
            ];
        }
        (new PermissionRecord)->insert($permissionRecords);
        (new PermissionMask)->insert($permissionMasks);

    }
}