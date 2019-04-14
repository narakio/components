<?php namespace Naraki\Oauth;

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

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'oauth');
        $this->app['router']->group([
            'namespace' => 'Naraki\Oauth\Controllers',
            'middleware' => ['web']
        ], function (Router $r) {
            $r->post('oauth/{driver}', 'OAuth@redirectToProvider')->name('oauth');
            $r->get('oauth/{driver}/callback', 'OAuth@handleProviderCallback')->name('oauth.callback');
            $r->post('oauth-yolo', 'OAuth@googleYolo');
            $r->post('oauth-yolo-dismiss', 'OAuth@googleYoloDismiss');
        });

    }
}