<?php namespace Naraki\System\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\System\Contracts\EventLog as SystemEventLogInterface;
use Naraki\System\Models\SystemEvent;
use Carbon\Carbon;

/**
 * Class SystemEventLog
 * @method \Naraki\System\Models\SystemEventLog createModel(array $attributes = [])
 */
class EventLog extends EloquentProvider implements SystemEventLogInterface
{
    protected $model = \Naraki\System\Models\SystemEventLog::class;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFromThisWeek()
    {
        $lastWeek = Carbon::now()->startOfWeek()->subWeek();
        return $this->select(['system_event_id', 'created_at', 'system_event_log_data'])
            ->where('created_at', '>', $lastWeek->toDateTimeString())
            ->orderBy('created_at', 'desc');
    }

    /**
     * @param int $eventID
     * @param array $data
     */
    public function log(int $eventID, $data = null)
    {
        $createdAt = Carbon::now();
        $dbData = [
            'system_event_id' => $eventID,
            'created_at' => $createdAt->toDateTimeString()
        ];
        if (!is_null($data)) {
            $dbData['system_event_log_data'] = $this->formatDataForStorage($data, $eventID, $createdAt);
        }
        $this->createModel()->newQuery()->insert($dbData);
    }

    /**
     * @param array $data
     * @param int $eventID
     * @param \Carbon\Carbon $createdAt
     * @return \stdClass
     */
    public function formatDataForStorage(array $data, int $eventID, Carbon $createdAt)
    {
        $output = new \stdClass();
        switch ($eventID) {
            case SystemEvent::NEWSLETTER_SUBSCRIPTION:
                $output->icon = 'address-book';
                $output->title = [
                    'messages.newsletter_subscribed',
                    [
                        'user' => $data['full_name'],
                        'email' => $data['email']
                    ]
                ];
                $output->message = [];
                break;
            case SystemEvent::CONTACT_FORM_MESSAGE:
                $output->icon = 'envelope';
                $output->title = [
                    'messages.contact_form_message',
                    [
                        'email' => $data['contact_email']
                    ]
                ];
                $output->message = [
                    [
                        'messages.contact_form_subject',
                        ['subject' => $data['contact_subject']]
                    ],
                    [
                        'messages.contact_form_message_content',
                        ['message' => $data['message_body']]
                    ]
                ];
                break;
        }
        $output->time = $createdAt->format('H:i');
        $output->date = $createdAt->toDateString();
        return serialize($output);
    }

}