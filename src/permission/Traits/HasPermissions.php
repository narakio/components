<?php namespace Naraki\Permission\Traits;

trait HasPermissions
{
    /**
     * Get permissions in a readable format i.e 'view/add/edit/delete'
     * as opposed to the binary value
     *
     * @param int $value
     * @param bool $toArray
     * @return array|string
     */
    public function getReadablePermissions($value = 65535, $toArray = false)
    {
        $permissions = self::getConstants('PERMISSION');
        $result = [];
        foreach ($permissions as $label => $permissionValue) {
            if (($value & $permissionValue) !== 0) {
                $result[trans(sprintf('internal.permissions.%s', $label))] = $permissionValue;
            }
        }
        return ($toArray) ? $result : implode('/', $result);
    }
}