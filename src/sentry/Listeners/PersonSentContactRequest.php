<?php namespace Naraki\Sentry\Listeners;

use Naraki\Core\Listener;
use Naraki\Mail\Emails\Frontend\Contact;
use Naraki\Sentry\Events\PersonSentContactRequest as ContactRequestEvent;
use Naraki\Mail\Jobs\SendMail;
use Naraki\System\Facades\System;
use Naraki\System\Models\SystemEvent;

class PersonSentContactRequest extends Listener
{
    /**
     *
     * @param \Naraki\Sentry\Events\PersonSentContactRequest $event
     * @return void
     */
    public function handle(ContactRequestEvent $event)
    {
        $data = [
            'contact_email' => $event->getContactEmail(),
            'contact_subject' => $event->getContactSubject(),
            'message_body' => $event->getMessageBody()
        ];
        $emailNotificationSubscribers = System::subscriptions()
            ->getSubscribedUsers(SystemEvent::CONTACT_FORM_MESSAGE);
        if (!$emailNotificationSubscribers->isEmpty()) {
            foreach ($emailNotificationSubscribers as $sub) {
                $data['user'] = $sub;

                $this->dispatch(
                    new SendMail(
                        new Contact($data)
                    )
                );
            }
        }
        System::log()->log(SystemEvent::CONTACT_FORM_MESSAGE, $data);
    }

}