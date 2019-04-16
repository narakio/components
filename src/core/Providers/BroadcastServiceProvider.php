<?php namespace Naraki\Core\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::channel('notifications.{id}', forward_static_call('static::notifications'));
    }


    public static function notifications()
    {
        /**
         * @param \Naraki\Sentry\Models\User $user
         * @param int $id
         * @return bool
         */
        return function ($user, $id) {
            if($user->shouldBeNotified(intval($id))){
                return true;
            }
            return false;
        };

    }
}
