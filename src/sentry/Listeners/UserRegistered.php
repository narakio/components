<?php namespace Naraki\Sentry\Listeners;

use Naraki\Core\Listener;
use Naraki\Sentry\Jobs\UpdateUserElasticsearch;
use Naraki\Mail\Emails\User\Welcome as WelcomeEmail;
use Naraki\Sentry\Events\UserRegistered as UserRegisteredEvent;
use Naraki\Mail\Jobs\SendMail;
use Naraki\Media\Jobs\CreateAvatar;

class UserRegistered extends Listener
{
    /**
     * Deleting all permissions and re-adding them including newly added/removed users
     *
     * @param \Naraki\Sentry\Events\UserRegistered $event
     * @return void
     */
    public function handle(UserRegisteredEvent $event)
    {
        //No token means the user was created through the backend, so it's probably an administrative user creation
        //where the user is trusted and will be activated right away without an email.
        if ($event->hasToken()) {
            $this->dispatch(
                new SendMail(
                    new WelcomeEmail([
                        'user' => $event->getUser(),
                        'activation_token' => $event->getToken()
                    ])
                )
            );
        }
        CreateAvatar::withChain([
            new UpdateUserElasticsearch(
                UpdateUserElasticsearch::WRITE_MODE_CREATE,
                $event->getUser()->getKey()
            )
        ])->dispatch($event->getUser()->getAttribute('username'),
            $event->getUser()->getAttribute('full_name'));
    }

}