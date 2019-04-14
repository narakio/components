<?php namespace Naraki\Permission;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Naraki\Permission\Commands\UpdatePermissions;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Contracts\Permission::class, Providers\Permission::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('command.naraki.permissions', function () {
            return new UpdatePermissions();
        });

        $this->commands(['command.naraki.permissions']);
    }

}