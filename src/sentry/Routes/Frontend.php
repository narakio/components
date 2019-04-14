<?php namespace Naraki\Sentry\Routes;

use Naraki\Core\Routes\Routes;
use Illuminate\Routing\Router;

class Frontend extends Routes
{
    public function bind(Router $router)
    {
        $availableLocales = config('app.locales');
        unset($availableLocales[app()->getLocale()]);
        $availableLocales[''] = '';
        foreach ($availableLocales as $k => $v) {
            $router->group([
                'prefix' => sprintf('/%s', $k),
                'middleware' => ['web'],
                'namespace' => 'Naraki\Sentry\Controllers\Frontend',
            ], call_user_func('static::defaultRouteGroup', $k));
        }
    }

    public static function defaultRouteGroup($locale)
    {
        return function (Router $r) use ($locale) {
            $r->group([
            ], call_user_func('static::auth', $locale));
        };
    }

    public static function auth($locale)
    {
        return function (Router $r) use ($locale) {
            $r->get(trans('routes.user', [], $locale), 'User@show')
                ->name(self::i18nRouteNames($locale, 'user'));
            $r->delete('user/delete', 'User@delete');
        };

    }

}