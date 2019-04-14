<?php namespace Naraki\Sentry\Listeners;

use Illuminate\Auth\Events\Login;
use Naraki\Sentry\Jobs\UpdateOnUserLogin;
use Naraki\Core\Listener;

class UserLogin extends Listener
{
    /**
     * @param \Illuminate\Auth\Events\Login $event
     */
    public function handle(Login $event)
    {
        $this->dispatch(new UpdateOnUserLogin(
            $event->guard, $event->user, $event->remember
        ));
    }
}