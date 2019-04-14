<?php namespace Naraki\Sentry;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    private $routeSets = [
        Routes\Ajax::class,
        Routes\Frontend::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Contracts\Group::class, Providers\Group::class);
        $this->app->singleton(Contracts\Person::class, Providers\Person::class);
        $this->app->singleton(Contracts\User::class, Providers\User::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        Gate::policy(Models\User::class, Policies\User::class);
        Gate::policy(Models\Group::class, Policies\Group::class);

        Auth::provider('NarakiUserProvider', function () {
            return app(Contracts\User::class);
        });

        $router = $this->app->make(Router::class);
        foreach ($this->routeSets as $binder) {
            $this->app->make($binder)->bind($router);
        }
    }

}