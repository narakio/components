<?php namespace Naraki\Core;

use Naraki\Permission\Models\PermissionMask;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class Policy
{
    use HandlesAuthorization;

    /**
     * @var int The processed permissions for the user, in the form of a bit mask
     */
    protected $defaultPermissions;

    /**
     * @var string
     */
    protected $model;

    public function __construct()
    {
        $model = $this->model;
        $this->defaultPermissions = PermissionMask::getDefaultPermission(
            auth()->user()->getAttribute('entity_type_id'),
            $model::$entityID
        );
    }

    public function __call($method, $arguments)
    {
        $constant = constant(
            sprintf('%s::%s',
                $this->model,
                sprintf('PERMISSION_%s', strtoupper($method))
            )
        );
        if (!is_int($constant)) {
            throw new \UnexpectedValueException(sprintf('Cannot apply method %s on %s', $method, $this->model));
        }
        return ($this->defaultPermissions & $constant) !== 0;
    }

}