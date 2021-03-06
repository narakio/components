<?php namespace Naraki\Permission\Support;

use Naraki\Core\Models\Entity;
use Naraki\Permission\Models\PermissionMask;
use Naraki\Permission\Models\PermissionRecord;
use Naraki\Permission\Models\PermissionStore;
use Illuminate\Support\Arr;

class User extends Permission
{
    public function __construct()
    {
        parent::__construct(Entity::USERS);
        //As a precaution, we initialize the following objects even though its very unlikely that they won't be overwritten down the line.
        $this->default = new PermissionStoreData(PermissionStoreData::DEFAULT);
        $this->computed = new PermissionStoreData(PermissionStoreData::COMPUTED);
    }

    /**
     * Public facing method to populate DB permissions
     *
     * @throws \Exception
     */
    public function assignPermissions()
    {
        $this->prepareAllForDb(
            $this->getUsersWithPermissions()
        );
        $this->populateDb();
    }

    /**
     * After all the permission processing, this method inserts permissions into the database.
     *
     * @throws \Exception
     */
    private function populateDb()
    {
        //There is only one default permission, which allows us not to have to create X times X permissions for everyone.
        $default = $this->default->get();
        $defaultPermissionStoreId = (new PermissionStore)->insertGetId([]);
        $permissionMasks = $permissionRecords = [];
        foreach ($default as $permission) {
            $permissionMasks[] = [
                'permission_store_id' => $defaultPermissionStoreId,
                'permission_holder_id' => $permission->getHolder(),
                'permission_mask' => $permission->getMask(),
                'permission_is_default' => true
            ];
            $permissionRecords[] = [
                'entity_id' => $this->entityId,
                'permission_target_id' => $permission->getHolder(),
                'permission_store_id' => $defaultPermissionStoreId
            ];
        }
        (new PermissionMask)->insert($permissionMasks);
        (new PermissionRecord)->insert($permissionRecords);
        unset($permissionMasks);

        $permissionRecords = [];
        //For users with permissions that needed some processing, we write them user by user.
        foreach ($this->allUsersInfo as $user) {
            if ($this->computed->has($user->entity_type_id)) {
                $computed = $this->computed->get($user->entity_type_id);
                $permissionStoreId = (new PermissionStore)->insertGetId([]);
                $tmp = [];
                foreach ($computed as $permission) {
                    $tmp[] = [
                        'permission_store_id' => $permissionStoreId,
                        'permission_holder_id' => $permission->getHolder(),
                        'permission_mask' => $permission->getMask()
                    ];
                }
                (new PermissionMask)->insert($tmp);
                $permissionRecords[] = [
                    'entity_id' => $this->entityId,
                    'permission_target_id' => $user->entity_type_id,
                    'permission_store_id' => $permissionStoreId
                ];
                //If there are no computed permissions for the user, we assign the default one.
            } else {
                $permissionRecords[] = [
                    'entity_id' => $this->entityId,
                    'permission_target_id' => $user->entity_type_id,
                    'permission_store_id' => $defaultPermissionStoreId
                ];
            }
        }
        (new PermissionRecord)->insert($permissionRecords);

        unset($this->allUsersInfo);
    }

    /**
     * @param $usersWithPermissions
     * @return void
     */
    private function prepareAllForDb($usersWithPermissions)
    {
        $users = $this->sqlGetUsersAndHighestGroup();
        $this->allUsersInfo = [];
        foreach ($users as $user) {
            $this->allUsersInfo[$user->user_id] = (object)$user->toArray();
        }
        $usersWithoutGroups = array_diff(array_keys($usersWithPermissions), array_keys($this->allUsersInfo));
        $usersWithoutGroupsInfo = $this->sqlGetWithoutGroupUserInfo($usersWithoutGroups);
        foreach ($usersWithoutGroupsInfo as $user) {
            $this->allUsersInfo[$user->user_id] = (object)$user->toArray();
        }
        unset($users);
        $defaultPermissions = null;
        $permissionsToInsert = [];
        foreach ($this->allUsersInfo as $permissionObject) {
            $usesDefaultPermissions = true;
            $tmp = [];
            foreach ($usersWithPermissions as $userWithPermission) {
                $permission = new PermissionData(
                    $permissionObject,
                    $this->allUsersInfo[$userWithPermission->user_id]
                );
                //A user with lower group status doesn't get to manipulate users above him.
                if ($permissionObject->group_mask < $this->allUsersInfo[$userWithPermission->user_id]->group_mask) {
                    $usesDefaultPermissions = false;
                    //A user can manipulate his own user entry.
                } elseif ($permissionObject->user_id == $userWithPermission->user_id) {
                    $permission->setMask($this->fullPermissionsBitmask);
                    $usesDefaultPermissions = false;
                    //In all other cases, regular defined permissions apply.
                } else {
                    $permission->setMask($userWithPermission->permission_mask);
                }
                $tmp[] = $permission;
            }
            //If no special permissions have been processed, we only need to store the default permission for this user.
            if ($usesDefaultPermissions) {
                if ($this->default->hasPermissions()) {
                    continue;
                }
                $this->default = new PermissionStoreData(
                    PermissionStoreData::DEFAULT, $tmp);
            } else {
                $permissionsToInsert[] = $tmp;
            }
        }
        //All users have been assessed and none of them have boilerplate permissions that we can use as default.
        //We go over the list and make sure every user has a default permission that matches what was set when submitting the form.
        if (!$this->default->hasPermissions()) {
            $tmp = [];
            foreach ($usersWithPermissions as $userWithPermission) {
                $p = new PermissionData(
                    $this->allUsersInfo[$userWithPermission->user_id],
                    $this->allUsersInfo[$userWithPermission->user_id]
                );
                $p->setMask($userWithPermission->permission_mask);
                $tmp[] = $p;
            }
//            dd(Arr::flatten($tmp, 1));
            $this->default = new PermissionStoreData(PermissionStoreData::DEFAULT,
                Arr::flatten($tmp, 1));
        }

        $this->computed = new PermissionStoreData(PermissionStoreData::COMPUTED,
            Arr::flatten($permissionsToInsert, 1));
    }

}