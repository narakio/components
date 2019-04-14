<?php namespace Naraki\Sitemap;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../config/sitemap.php' => config_path('sitemap.php'),
        ], 'config');

//        $this->app('storage')->copy(__DIR__.'/../resources/xsl/sitemap.xsl',public_path('resources'));
    }

    public function boot()
    {
        $this->app['router']->group([
            'middleware' => 'misc',
        ], function ($r) {
            $r->get('sitemap/{type?}/{slug?}', ['uses' => __NAMESPACE__ . '\Controllers\Sitemap'])->name('sitemap');
        });
    }

}