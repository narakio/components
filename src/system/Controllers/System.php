<?php namespace Naraki\System\Controllers;

use Naraki\Core\Controllers\Admin\Controller;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Naraki\System\Facades\System as SystemProvider;

class System extends Controller
{

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getLog()
    {
        $events = SystemProvider::log()->getFromThisWeek()->get();
        $schedule = [];
        if (!is_null($events)) {
            $today = Carbon::now()->setTime(0, 0, 0);
            $yesterday = Carbon::now()->yesterday();
            $thisWeek = Carbon::now()->startOfWeek();
            foreach ($events as $event) {
                $date = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $event->getAttribute('created_at')
                )->setTime(0, 0, 0);
                $data = unserialize($event->getAttribute('system_event_log_data'));
                $output = clone($data);
                $output->title = trans($data->title[0], $data->title[1]);
                $output->message = [];
                foreach ($data->message as $msg) {
                    $output->message[] = trans($msg[0], $msg[1]);
                }

                if ($date->eq($today)) {
                    unset($output->date);
                    $schedule[trans('general.time.today')][] = $output;
                } elseif ($date->eq($yesterday)) {
                    unset($output->date);
                    $schedule[trans('general.time.yesterday')][] = $output;
                } elseif ($date->lt($yesterday) && $date->gte($thisWeek)) {
                    $schedule[trans('general.time.this_week')][] = $output;
                } else {
                    $schedule[trans('general.time.last_week')][] = $output;
                }
            }
        }
        return response(['events' => $schedule], Response::HTTP_OK);
    }
}