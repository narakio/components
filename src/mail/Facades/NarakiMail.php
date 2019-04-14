<?php namespace Naraki\Mail\Facades;

use Naraki\Mail\Contracts\Email as EmailContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Naraki\Mail\Providers\Subscriber subscriber()
 * @method static \Naraki\Mail\Providers\Schedule schedule()
 * @method static \Naraki\Mail\Providers\Listing list()
 * @method static mixed yieldEmailContent($targetID, $emailEventID = null)
 */
class NarakiMail extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return EmailContract::class;
    }
}