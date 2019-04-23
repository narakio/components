<?php namespace Naraki\System\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    /**
     * TODO: this method is not used but might be in the future if a home slider gets built
     *
     * @param $name
     * @return array
     */
    public function searchFeaturableEntities($name)
    {
        return DB::select('
select entity_types.entity_type_id,entity_name from entity_types
join (
select entity_type_id, blog_post_title as entity_name
from blog_posts
join entity_types on entity_types.entity_type_target_id = blog_posts.blog_post_id
and entity_id = ?
    and blog_post_title like ?
) bp on entity_types.entity_type_id = bp.entity_type_id',
            [Entity::BLOG_POSTS,sprintf('%%%s%%',$slug)]
        );
    }

}