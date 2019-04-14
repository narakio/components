<?php namespace Naraki\System\Facades;

use Illuminate\Support\Facades\Facade;
use Naraki\System\Contracts\System as SystemContract;

/**
 * @method static \Naraki\System\Providers\UserSubscriptions subscriptions()
 * @method static \Naraki\System\Providers\EventLog log()
 */
class System extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return SystemContract::class;
    }

}