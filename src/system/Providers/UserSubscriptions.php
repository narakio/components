<?php namespace Naraki\System\Providers;

use Illuminate\Support\Facades\Cache;
use Naraki\Core\EloquentProvider;
use Naraki\Core\Models\Entity;
use Naraki\System\Contracts\UserSubscriptions as SystemInterface;
use Naraki\System\Models\SystemEvent;
use Naraki\System\Models\SystemEventType;
use Naraki\System\Models\SystemUserSubscriptions;

class UserSubscriptions extends EloquentProvider implements SystemInterface
{
    protected $model = \Naraki\System\Models\SystemUserSubscriptions::class;

    /**
     * @param int $userID
     * @param int $entityID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSubscriptions($userID, $entityID = Entity::SYSTEM)
    {
        return $this->build()
            ->scopes(['systemEvent'])
            ->where('user_id', $userID)
            ->where('entity_id', $entityID);
    }

    /**
     * @param int $event
     * @param int $entityID
     * @param int $eventType
     * @return \Naraki\System\Models\SystemUserSubscriptions[]|\Illuminate\Support\Collection
     */
    public function getSubscribedUsers($event, $entityID = Entity::SYSTEM, $eventType = SystemEventType::EMAIL)
    {
        $dbResult = $this->buildWithScopes(['email', 'full_name'],
            ['systemEvent', 'user'])
            ->where('system_events.system_event_id', $event)
            ->where('entity_id', $entityID)
            ->where('system_event_type_id', $eventType)
            ->get();
        return $dbResult;
    }

    /**
     * @param $userID
     * @return array
     */
    public function getLiveNotifications($userID)
    {
        return $this->select(['system_event_id'])
            ->where('user_id', $userID)
            ->where('system_event_type_id', SystemEventType::BROADCAST)
            ->pluck('system_event_id')->toArray();
    }

    /**
     * @param int $userID
     * @param string $cacheKeyPrefix
     * @param string $fetchMethod
     * @param bool $force
     * @return array
     */
    private function cacheSubscriptions($userID, $cacheKeyPrefix, $fetchMethod, $force = false)
    {
        $cacheKey = sprintf('%s.%s', $cacheKeyPrefix, $userID);
        $subs = Cache::get($cacheKey);
        if (is_null($subs) || $force) {
            Cache::put($cacheKey, $this->{$fetchMethod}($userID), 3600);
        }
        return $subs;
    }

    /**
     * @param $userID
     * @param bool $force
     * @return array
     */
    public function cacheLiveNotifications($userID, $force = false)
    {
        return $this->cacheSubscriptions(
            $userID,
            'events_subscribed',
            'getLiveNotifications',
            $force
        );
    }

    /**
     * @param int $userID
     * @param int $entityId
     * @return array
     */
    public function getFrontendSubscriptions($userID, $entityId = Entity::BLOG_POSTS)
    {
        $subs = $this->select(['system_events.system_event_id'])
            ->scopes(['systemEvent'])
            ->where('user_id', $userID)
            ->where('system_event_type_id', SystemEventType::EMAIL)
            ->where('entity_id', $entityId)
            ->pluck('system_event_id')->toArray();
        $subscriptions = [];
        foreach ($subs as $intval) {
            $subscriptions[SystemEvent::getConstantName(intval($intval))] = true;
        }
        return $subscriptions;
    }

    /**
     * @param int $userID
     * @param bool $force
     * @return array
     */
    public function cacheFrontendSubscriptions($userID, $force = false)
    {
        return $this->cacheSubscriptions(
            $userID,
            'events_subscribed_frontend',
            'getFrontendSubscriptions',
            $force
        );
    }

    /**
     * @param int $userID
     * @param array $data
     * @return array
     */
    public function saveBackend($userID, $data)
    {
        if ($this->save($userID, $data)) {
            return $this->cacheLiveNotifications($userID, true);
        }
        return $this->cacheLiveNotifications($userID);
    }

    /**
     * @param int $userID
     * @param array $data
     * @param int $entityId
     * @return array
     */
    public function saveFrontend($userID, $data, $entityId = Entity::BLOG_POSTS)
    {
        if (empty($data)) {
            return [];
        }
        $events = [];
        foreach ($data as $key => $item) {
            $const = SystemEvent::getConstant($key);
            if (is_int($const) && $item == 'true') {
                $events[] = $const;
            }
        }

        $dbSubscriptions = $this
            ->getSubscriptions($userID, $entityId)
            ->where('system_event_type_id', SystemEventType::EMAIL)
            ->select([
                'system_user_subscription_id as id',
                'system_events.system_event_id as event',
            ])
            ->get()->pluck('event', 'id')->toArray();


        $subsToAdd = $subsToRemove = [];
        $tmp = array_diff($events, $dbSubscriptions);
        foreach ($tmp as $v) {
            $subsToAdd[] = [
                'user_id' => $userID,
                'system_event_type_id' => SystemEventType::EMAIL,
                'system_event_id' => $v
            ];
        }

        $tmp = array_diff($dbSubscriptions, $events);
        foreach ($tmp as $k => $v) {
            $subsToRemove[] = $k;
        }

        $hasChanged = false;
        if (!empty($subsToAdd)) {
            SystemUserSubscriptions::insert($subsToAdd);
            $hasChanged = true;
        }
        if (!empty($subsToRemove)) {
            SystemUserSubscriptions::query()->select(['system_user_subscription_id'])
                ->whereKey($subsToRemove)->delete();
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $this->cacheFrontendSubscriptions($userID, true);
        }
        return $this->cacheFrontendSubscriptions($userID);
    }

    /**
     * @param int $userId
     * @param array $data
     * @param int $entityID
     * @return bool
     */
    public function save($userId, $data, $entityID = Entity::SYSTEM)
    {
        $subsToSave = $subsExisting = [
            SystemEventType::BROADCAST => [],
            SystemEventType::EMAIL => []
        ];
        $setting = 'events';
        if (isset($data[$setting]) && is_array($data[$setting])) {
            $subsToSave[SystemEventType::BROADCAST] = $data[$setting];
        }
        $setting = 'email';
        if (isset($data[$setting]) && is_array($data[$setting])) {
            $subsToSave[SystemEventType::EMAIL] = $data[$setting];
        }

        $dbSubscriptions = $this
            ->getSubscriptions($userId, $entityID)
            ->select([
                'system_user_subscription_id as id',
                'system_events.system_event_id as event',
                'system_event_type_id as type'
            ])
            ->get();

        foreach ($dbSubscriptions as $sub) {
            $subsExisting[intval($sub->getAttribute('type'))]
            [intval($sub->getAttribute('id'))] =
                $sub->getAttribute('event');
        }

        $subsToAdd = $subsToRemove = [];
        foreach ($subsToSave as $type => $sub) {
            $tmp = array_diff($sub, $subsExisting[$type]);
            foreach ($tmp as $v) {
                $subsToAdd[] = [
                    'user_id' => $userId,
                    'system_event_type_id' => $type,
                    'system_event_id' => $v
                ];
            }

            $tmp = array_diff($subsExisting[$type], $sub);
            foreach ($tmp as $k => $v) {
                $subsToRemove[] = $k;
            }
        }

        $hasChanged = false;
        if (!empty($subsToAdd)) {
            SystemUserSubscriptions::insert($subsToAdd);
            $hasChanged = true;
        }
        if (!empty($subsToRemove)) {
            SystemUserSubscriptions::query()->select(['system_user_subscription_id'])
                ->whereKey($subsToRemove)->delete();
            $hasChanged = true;
        }

        return $hasChanged;
    }

}