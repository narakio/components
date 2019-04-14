<?php namespace Naraki\Forum;

use Illuminate\Routing\Router;
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
        $this->app->singleton(Contracts\Board::class, Providers\Board::class);
        $this->app->singleton(Contracts\Thread::class, Providers\Thread::class);
        $this->app->singleton(Contracts\Post::class, Providers\Post::class);
        $this->app->singleton(Contracts\Forum::class, Providers\Forum::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        app('events')->listen(Events\PostCreated::class,Listeners\PostCreated::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'forum');

        app('router')->group([
            'prefix' => '/ajax/',
            'namespace' => 'Naraki\Forum\Controllers',
            'middleware' => ['web', 'frontend_auth']
        ], function (Router $r) {
            $r->post('forum/{entity_type}/{slug}/comment', 'Post@createComment');
            $r->patch('forum/{entity_type}/{slug}/comment', 'Post@updateComment');
            $r->patch('forum/{entity_type}/options', 'Post@updateNotifications');
            $r->delete('forum/{entity_type}/{slug}/comment/{comment}', 'Post@deleteComment');
            $r->patch('forum/{entity_type}/{slug}/favorite/{comment}', 'Post@favorite');
            $r->delete('forum/{entity_type}/{slug}/favorite/{comment}', 'Post@unfavorite');
        });

        $this->app['router']->group([
            'prefix' => '/ajax/',
            'namespace' => 'Naraki\Forum\Controllers',
            'middleware' => ['web']
        ], function (Router $r) {
            $r->get('forum/{entity_type}/{slug}/comment/{sort?}/{order?}', 'Post@getComments');
        });
    }

}