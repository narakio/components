<?php namespace Naraki\Sentry\Events;

use Naraki\System\Models\SystemEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserSubscribedToNewsletter implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $input;

    /**
     * Create a new event instance.
     *
     * @param $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    public function broadcastOn()
    {
        return new PrivateChannel(
            sprintf('notifications.%s',SystemEvent::NEWSLETTER_SUBSCRIPTION)
        );
    }

    public function broadcastAs()
    {
        return 'default';
    }

    public function broadcastWith()
    {
        return [
            'title' => trans('messages.newsletter_subscribed',
                [
                    'user' => $this->input['full_name'],
                    'email' => $this->input['email']
                ])
        ];
    }

}
