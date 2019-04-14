<?php namespace Naraki\Sentry\Policies;

use Naraki\Core\Policy;
use Naraki\Permission\Models\PermissionMask;

class User extends Policy
{
    protected $model = \Naraki\Sentry\Models\User::class;

    /**
     * @return bool
     */
    public function edit()
    {
        return $this->checkPermissions(constant(
            sprintf('%s::PERMISSION_EDIT',
                $this->model)
        ));
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->checkPermissions(constant(
            sprintf('%s::PERMISSION_DELETE',
                $this->model)
        ));
    }

    /**
     * @param $constant
     * @return bool
     */
    private function checkPermissions($constant)
    {
        $model = $this->model;
        //the default permissions are used to assess the permission of a user against
        //all other users. We do a bitwise comparison versus the edit permission bitmask
        if (($this->defaultPermissions & $constant) !== 0) {
            //In some cases, users will have specific permissions on other users when those users
            //belong to groups higher than them, i.e regular users can't edit root or superadmin accounts
            $targetPermission = PermissionMask::getTargetPermission(
                auth()->user()->getAttribute('entity_type_id'),
                $model::$entityID,
                app('router')->getCurrentRoute()->parameter('user')
            );
            //If the query returns null, there are no particular permissions set, we can defer
            //to the default permission, which in this case is true.
            if (is_int($targetPermission)) {
                //If the query returns a number, we need to do a bitwise comparison.
                //Very likely that number will be 0, meaning no permissions.
                return (($targetPermission & $constant) !== 0);
            }
            return true;
        }
        return false;
    }
}
