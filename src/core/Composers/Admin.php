<?php namespace Naraki\Core\Composers;

use Naraki\Core\Composer;
use Naraki\Core\Facades\JavaScript;
use Naraki\Permission\Facades\Permission;
use Naraki\Sentry\Models\User;
use Naraki\System\Facades\System;

class Admin extends Composer
{

    /**
     * @param \Illuminate\View\View $view
     */
    public function compose($view)
    {
        $tmp = auth()->user();
        $user = null;
        if ($tmp instanceof User) {
            $user = $tmp->only(['username']);
        }
        $isAuthenticated = auth('jwt')->check();
        $subs = [];

        if ($isAuthenticated) {
            $subs = System::subscriptions()
                ->cacheLiveNotifications(auth()->user()->getKey());
        }
        $user['events_subscribed'] = $subs;

        JavaScript::putArray([
            'appName' => config('app.name'),
            'locale' => app()->getLocale(),
            'user' => $user,
            'permissions' => $isAuthenticated
                ? Permission::cacheUserPermissions(auth('jwt')->user()->getAttribute('entity_type_id'))
                : null
        ]);

        JavaScript::bindJsVariablesToView();
    }


}