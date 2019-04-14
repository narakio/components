<?php namespace Naraki\Rss;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/rss.php' => config_path('rss.php'),
        ], 'config');
    }

    public function boot()
    {
        $this->app['router']->group([
            'middleware' => 'misc',
        ], function ($r) {
            $r->get('rss/{type}/{slug?}', ['uses' => __NAMESPACE__ . '\Controllers\Rss'])->name('rss');
        });
    }

}