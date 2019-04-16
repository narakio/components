<?php namespace Naraki\Permission;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

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
        app('events')->listen(
            Events\PermissionEntityUpdated::class,
            Listeners\UpdatePermissions::class
        );
        $this->app->singleton('command.naraki.permissions', function () {
            return new Commands\UpdatePermissions();
        });

        $this->commands(['command.naraki.permissions']);
    }

}