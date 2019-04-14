<?php namespace Naraki\System\Models;

use Naraki\Core\Contracts\Enumerable as EnumerableContract;
use Naraki\Core\Contracts\HasAnEntity as HasAnEntityContract;
use Naraki\Core\Traits\Enumerable;
use Naraki\Core\Traits\Models\HasAnEntity as HasAnEntity;
use Naraki\Permission\Contracts\HasPermissions as HasPermissionsContract;
use Naraki\Permission\Traits\HasPermissions as HasPermissions;

/**
 * NOT REALLY A MODEL, BUT WE LEAVE IT HERE ANYWAY, DEAL WITH IT
 */
class System implements EnumerableContract, HasPermissionsContract, HasAnEntityContract
{
    use Enumerable, HasPermissions, HasAnEntity;

    public static $entityID = \Naraki\Core\Models\Entity::SYSTEM;

    const PERMISSION_LOGIN = 0b1;
    const PERMISSION_SETTINGS = 0b10;
    const PERMISSION_PERMISSIONS = 0b100;
    const PERMISSION_NOTIFICATIONS = 0b1000;

}