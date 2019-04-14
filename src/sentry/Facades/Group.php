<?php namespace Naraki\Sentry\Facades;

use Illuminate\Support\Facades\Facade;
use Naraki\Sentry\Contracts\Group as GroupContract;

class Group extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return GroupContract::class;
    }

}