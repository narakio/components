<?php namespace Naraki\Core\Support\JavaScript;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Naraki\Core\Facades\JavaScript;
use Naraki\Core\Support\JavaScript\Transformers\Transformer;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('JavaScript', function ($app) {
            return new Transformer(
                new LaravelViewBinder(
                    $app['events'],
                    ['core::partials.javascript_footer']
                ),
                'config'
            );
        });
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        AliasLoader::getInstance()->alias(
            'JavaScript',
            JavaScript::class
        );
    }

}