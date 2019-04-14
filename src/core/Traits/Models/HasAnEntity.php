<?php namespace Naraki\Core\Traits\Models;

trait HasAnEntity
{
    /**
     * Classes using this trait should define a entityID
     * whose value matches a record in the entities table
     *
     * @see \Naraki\Core\Models\Entity
     * @return int
     */
    public function getEntity()
    {
        return static::$entityID;
    }

}
