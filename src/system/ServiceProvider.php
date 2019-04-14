<?php namespace Naraki\System;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(Contracts\EventLog::class, Providers\EventLog::class);
        $this->app->singleton(Contracts\UserSubscriptions::class, Providers\UserSubscriptions::class);
        $this->app->singleton(Contracts\System::class, Providers\System::class);
    }

    public function boot()
    {
        Gate::policy(Models\System::class, Policies\System::class);

        $this->app['router']->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\System\Controllers',
            'middleware' => ['admin_auth', 'admin']
        ], function (Router $r) {
            $r->get('user/general', 'General@edit');
            $r->patch('user/general', 'General@update');
            $r->patch('user/password', 'Password@update');
            $r->patch('user/profile', 'Profile@update');
            $r->get('user/avatar', 'Profile@avatar');
            $r->patch('user/avatar', 'Profile@setAvatar');
            $r->delete('user/avatar/{uuid}', 'Profile@deleteAvatar');

            $r->get('system/events/log', 'System@getLog');
        });

        $this->app['router']->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\System\Controllers',
            'middleware' => ['admin_auth', 'admin', 'can:settings,Naraki\System\Models\System']
        ], function (Router $r) {
            $r->get('settings/general', 'Settings@edit');
            $r->post('settings/general', 'Settings@update');
            $r->get('settings/social', 'Settings@editSocial');
            $r->post('settings/social', 'Settings@updateSocial');
            $r->get('settings/sitemap', 'Settings@editSitemap');
            $r->post('settings/sitemap', 'Settings@updateSitemap');
        });

    }

}