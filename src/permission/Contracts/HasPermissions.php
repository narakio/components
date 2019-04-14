<?php namespace Naraki\Permission\Contracts;

interface HasPermissions
{
    public function getReadablePermissions($value = 65535, $toArray = false);

}