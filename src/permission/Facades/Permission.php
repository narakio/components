<?php namespace Naraki\Permission\Facades;

use Illuminate\Support\Facades\Facade;
use Naraki\Permission\Contracts\Permission as PermissionContract;

/**
 * @method static \stdClass getAllUserPermissions($entityTypeId)
 */
class Permission extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return PermissionContract::class;
    }

}