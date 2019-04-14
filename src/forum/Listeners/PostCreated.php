<?php namespace Naraki\Forum\Listeners;

use Naraki\Core\Listener;
use Naraki\Forum\Events\PostCreated as PostCreatedEvent;
use Naraki\Forum\Jobs\NotifyMentionees;
use Naraki\Forum\Jobs\NotifyReplySubscribers;

class PostCreated extends Listener
{
    public function handle(PostCreatedEvent $event)
    {
        if ($event->request->hasMentions()) {
            $this->dispatch(new NotifyMentionees($event));
        }
        $this->dispatch(new NotifyReplySubscribers($event));
    }

}