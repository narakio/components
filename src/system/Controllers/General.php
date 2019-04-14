<?php namespace Naraki\System\Controllers;

use Naraki\Core\Controllers\Admin\Controller;
use Illuminate\Http\Response;
use Naraki\System\Facades\System;
use Naraki\System\Models\SystemEvent;
use Naraki\System\Models\SystemEventType;

class General extends Controller
{
    /**
     * Update the user's password.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        System::subscriptions()
            ->saveBackend($this->user->getKey(), app('request')->all());
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit()
    {
        $settings = ['events' => [], 'email' => []];
        $dbSubscriptions = System::subscriptions()
            ->getSubscriptions($this->user->getKey())
            ->select(['system_events.system_event_id as event','system_event_type_id as type'])
            ->get();

        if (!is_null($dbSubscriptions)) {
            $dbSubArray = $dbSubscriptions->toArray();
            foreach($dbSubArray as $sub){
                switch ( $sub['type'] ) {
                    case SystemEventType::BROADCAST:
                    $settings['events'][] = $sub['event'];
                    break;
                    case SystemEventType::EMAIL:
                    $settings['email'][] = $sub['event'];
                    break;
                }
            }
        }

        $eventsDb = System::getEvents();

        $events = [];
        foreach ($eventsDb as $event) {
            $events[] = [
                'id' => $event->getKey(),
                'name' => SystemEvent::getConstantName($event->getKey(), true)
            ];
        }

        return response([
            'existing' => $settings,
            'events' => $events
        ], Response::HTTP_OK);
    }
}
