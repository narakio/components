<?php namespace Naraki\Sentry\Facades;

use Illuminate\Support\Facades\Facade;
use Naraki\Sentry\Contracts\User as UserContract;

/**
 * @method static \Naraki\Sentry\Contracts\Person|\Naraki\Sentry\Providers\Person person()
 */
class User extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return UserContract::class;
    }

}