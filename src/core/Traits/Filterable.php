<?php namespace Naraki\Core\Traits;

trait Filterable
{
    /**
     * @return \Naraki\Core\Filters
     */
    public function newFilter()
    {
        return new $this->filter;
    }
}