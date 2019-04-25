<?php namespace Naraki\Components;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nk');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nk');
        if ($this->app->runningInConsole()) {
            $this->app->make(Factory::class)->load(__DIR__ . '/../database/factories');
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}