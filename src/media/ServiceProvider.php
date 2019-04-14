<?php namespace Naraki\Media;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    private $routeSets = [
        Routes\Admin::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Contracts\Avatar::class, Providers\Avatar::class);
        $this->app->singleton(Contracts\Text::class, Providers\Text::class);
        $this->app->singleton(Contracts\File::class, Providers\File::class);
        $this->app->singleton(Contracts\Image::class, Providers\Image::class);
        $this->app->singleton(Contracts\Media::class, Providers\Media::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        Gate::policy(
            Models\MediaEntity::class,
            Policies\Media::class
        );

        $router = $this->app->make(Router::class);
        foreach ($this->routeSets as $binder) {
            $this->app->make($binder)->bind($router);
        }
    }
}