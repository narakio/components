<?php namespace Naraki\Blog;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Naraki\Blog\Composers\Post as BlogComposer;
use Naraki\Blog\Composers\Home as HomeComposer;

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
        $this->app->singleton(Contracts\Category::class, Providers\Category::class);
        $this->app->singleton(Contracts\Tag::class, Providers\Tag::class);
        $this->app->singleton(Contracts\Source::class, Providers\Source::class);
        $this->app->singleton(Contracts\Author::class, Providers\Author::class);
        $this->app->singleton(Contracts\Blog::class, Providers\Blog::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'blog');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/blog'),
            ]);
            $this->loadMigrationsFrom(__DIR__ . '/resources/migrations');
        }

        Gate::policy(Models\BlogPost::class, Policies\BlogPost::class);
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'blog');
        $this->app->make('view')->composer(['blog::post'], BlogComposer::class);

        $router = $this->app->make(Router::class);
        foreach ($this->routeSets as $binder) {
            $this->app->make($binder)->bind($router);
        }
    }
}