<?php namespace Naraki\Sentry\Events;

use Naraki\Core\Event;
use Naraki\System\Models\SystemEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonSentContactRequest extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    private $contactEmail;
    /**
     * @var string
     */
    private $contactSubject;
    /**
     * @var string
     */
    private $messageBody;

    /**
     *
     * @param string $email
     * @param string $subject
     * @param string $messageBody
     */
    public function __construct($email, $subject, $messageBody)
    {
        $this->contactEmail = $email;
        $this->contactSubject = $subject;
        $this->messageBody = $messageBody;
    }

    public function broadcastOn()
    {
        return new PrivateChannel(
            sprintf('notifications.%s',SystemEvent::CONTACT_FORM_MESSAGE)
        );

    }

    public function broadcastAs()
    {
        return 'default';
    }

    public function broadcastWith()
    {
        return [
            'title' => trans('messages.contact_form_message',
                [
                    'email' => $this->contactEmail
                ]),
        ];
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    /**
     * @return string
     */
    public function getContactSubject(): string
    {
        return $this->contactSubject;
    }

    /**
     * @return string
     */
    public function getMessageBody(): string
    {
        return $this->messageBody;
    }


}