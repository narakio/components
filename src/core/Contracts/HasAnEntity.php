<?php namespace Naraki\Core\Contracts;

interface HasAnEntity
{
    /**
     * Classes using this trait should define a entityID
     * whose value matches a record in the entities table
     *
     * @see \Naraki\Core\Models\Entity
     * @return int
     */
    public function getEntity();

}
