<?php namespace Naraki\Mail\Providers;

use Naraki\Mail\Models\EmailList;
use Naraki\Mail\Models\EmailRecipientType;
use Naraki\Core\EloquentProvider;
use Illuminate\Support\Str;
use Naraki\Mail\Contracts\Email as EmailInterface;
use Naraki\Mail\Contracts\Listing as ListInterface;
use Naraki\Mail\Contracts\Schedule as ScheduleInterface;
use Naraki\Mail\Contracts\Subscriber as SubscriberInterface;

class Email extends EloquentProvider implements EmailInterface
{
    protected $model = \Naraki\Mail\Models\Email::class;
    /**
     * @var \Naraki\Mail\Contracts\Subscriber|\Naraki\Mail\Providers\Subscriber
     */
    protected $subscriber;
    /**
     * @var \Naraki\Mail\Contracts\Schedule|\Naraki\Mail\Providers\Schedule
     */
    protected $schedule;
    /**
     * @var \Naraki\Mail\Contracts\Listing|\Naraki\Mail\Providers\Listing
     */
    protected $list;

    public function __construct(
        SubscriberInterface $sui,
        ScheduleInterface $sci,
        ListInterface $eli,
        $model = null
    ) {
        parent::__construct($model);
        $this->subscriber = $sui;
        $this->schedule = $sci;
        $this->list = $eli;
    }

    /**
     * @return \Naraki\Mail\Contracts\Subscriber|\Naraki\Mail\Providers\Subscriber
     */
    public function subscriber(): SubscriberInterface
    {
        return $this->subscriber;
    }

    /**
     * @return \Naraki\Mail\Contracts\Schedule|\Naraki\Mail\Providers\Schedule
     */
    public function schedule(): ScheduleInterface
    {
        return $this->schedule;
    }

    /**
     * @return \Naraki\Mail\Contracts\Listing|\Naraki\Mail\Providers\Listing
     */
    public function list(): ListInterface
    {
        return $this->list;
    }

    /**
     * Gets content to be displayed in emails. Calls the appropriate function based
     * on the type of email being sent.
     *
     * @param int $targetID
     * @param int $emailEventID
     *
     * @return mixed
     * @throws \Exception
     */
    public function yieldEmailContent($targetID, $emailEventID = null)
    {
        $collection = call_user_func(
            [$this, Str::camel(EmailList::getConstantName($emailEventID))],
            $targetID
        );
        if (!$collection->has('recipient_type')) {
            $collection->put('recipient_type', EmailRecipientType::ALL);
        }

        return $collection;
    }

}