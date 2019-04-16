<?php namespace Naraki\Core;

use Illuminate\Foundation\Http\Kernel as LaravelHttpKernel;

class HttpKernel extends LaravelHttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Naraki\Core\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Naraki\Core\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Naraki\Core\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Naraki\Core\Middleware\SetLocale::class,
//            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Naraki\Core\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'admin' => [
            \Naraki\Core\Middleware\SetLocale::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'misc' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @see \Illuminate\Auth\Middleware\Authenticate::class
     * @var array
     */
    protected $routeMiddleware = [
        'frontend_auth' => \Naraki\Core\Middleware\Frontend\Auth::class,
        'frontend_guest'=> \Naraki\Core\Middleware\Frontend\Guest::class,
        'admin_auth' => \Naraki\Core\Middleware\Admin\Auth::class,

        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
