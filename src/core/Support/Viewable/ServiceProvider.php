<?php namespace Naraki\Core\Support\Viewable;

use CyrildeWit\EloquentViewable\Contracts\CrawlerDetector as CrawlerDetectorContract;
use CyrildeWit\EloquentViewable\Contracts\HeaderResolver as HeaderResolverContract;
use CyrildeWit\EloquentViewable\Contracts\IpAddressResolver as IpAddressResolverContract;
use CyrildeWit\EloquentViewable\CrawlerDetectAdapter;
use CyrildeWit\EloquentViewable\Resolvers\HeaderResolver;
use CyrildeWit\EloquentViewable\Resolvers\IpAddressResolver;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->when(View::class)
            ->needs(CacheRepository::class)
            ->give(function () : CacheRepository {
                return $this->app['cache']->store(config('eloquent-viewable.cache.store'));
            });

        $this->app->bind(CrawlerDetectAdapter::class, function ($app) {
            $detector = new CrawlerDetect(
                $app['request']->headers->all(),
                $app['request']->server('HTTP_USER_AGENT')
            );

            return new CrawlerDetectAdapter($detector);
        });

        $this->app->singleton(CrawlerDetectorContract::class, CrawlerDetectAdapter::class);
        $this->app->singleton(IpAddressResolverContract::class, IpAddressResolver::class);
        $this->app->singleton(HeaderResolverContract::class, HeaderResolver::class);
    }
}
