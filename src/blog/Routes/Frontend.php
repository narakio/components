<?php namespace Naraki\Blog\Routes;

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
                'namespace' => 'Naraki\Blog\Controllers\Frontend',
            ], call_user_func('static::defaultRouteGroup', $k));
        }
    }

    public static function defaultRouteGroup($locale)
    {
        return function (Router $r) use ($locale) {
            $r->group([
            ], call_user_func('static::guest', $locale));
        };
    }

    public static function guest($locale)
    {
        return function (Router $r) use ($locale) {
            $r->get(trans('routes.blog_slug', [], $locale), 'Blog@getPost')
                ->name(self::i18nRouteNames($locale, 'blog'));
            $r->get(trans('routes.blog_cat', [], $locale), 'Blog@category')
                ->name(self::i18nRouteNames($locale, 'blog.category'));
            $r->get(trans('routes.blog_tag', [], $locale), 'Blog@tag')
                ->name(self::i18nRouteNames($locale, 'blog.tag'));
            $r->get(trans('routes.blog_author', [], $locale), 'Blog@author')
                ->name(self::i18nRouteNames($locale, 'blog.author'));
        };

    }

}