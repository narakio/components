<?php namespace Naraki\System\Providers;

use Illuminate\Support\Facades\Cache;
use Naraki\Core\EloquentProvider;
use Naraki\Core\Models\Entity;
use Naraki\System\Contracts\System as SystemInterface;
use Naraki\System\Contracts\EventLog as SystemEventLogInterface;
use Naraki\System\Contracts\UserSubscriptions as SystemUserSettingsInterface;
use Naraki\System\Models\SystemEvent;

class System extends EloquentProvider implements SystemInterface
{
    protected $model = \Naraki\System\Models\SystemEvent::class;

    /**
     * @var \Naraki\System\Contracts\EventLog
     */
    private $log;
    /**
     * @var \Naraki\System\Contracts\UserSubscriptions|\Naraki\System\Providers\UserSubscriptions
     */
    private $userSubscriptions;

    /**
     *
     * @param \Naraki\System\Contracts\EventLog|\Naraki\System\Providers\EventLog $log
     * @param \Naraki\System\Contracts\UserSubscriptions|\Naraki\System\Providers\UserSubscriptions $ssi
     */
    public function __construct(SystemEventLogInterface $log, SystemUserSettingsInterface $ssi)
    {
        parent::__construct();
        $this->log = $log;
        $this->userSubscriptions = $ssi;
    }

    /**
     * @return \Naraki\System\Contracts\EventLog|\Naraki\System\Providers\EventLog
     */
    public function log()
    {
        return $this->log;
    }

    /**
     * @return \Naraki\System\Contracts\UserSubscriptions|\Naraki\System\Providers\UserSubscriptions
     */
    public function subscriptions()
    {
        return $this->userSubscriptions;
    }

    /**
     * @param int $entityID
     * @return SystemEvent[]
     */
    public function getEvents($entityID = Entity::SYSTEM)
    {
        return SystemEvent::query()
            ->select(['system_event_id', 'system_event_name as name'])
            ->where('entity_id', $entityID)
            ->get();
    }

    /**
     * @return array
     */
    public function getFrontendEvents()
    {
        if (Cache::has('frontend_blog_events')) {
            return Cache::get('frontend_blog_events');
        } else {
            $eventsDb = System::getEvents(Entity::BLOG_POSTS);
            $events = [];
            foreach ($eventsDb as $event) {
                $events[] = [
                    'id' => SystemEvent::getConstantName($event->getKey()),
                    'name' => SystemEvent::getConstantName($event->getKey(), true)
                ];
            }
            Cache::put('frontend_blog_events', $events, 2600000);
        }
        return $events;
    }

}