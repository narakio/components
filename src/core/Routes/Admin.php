<?php namespace Naraki\Core\Routes;

use Illuminate\Routing\Router;

class Admin extends Routes
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/admin',
                'namespace' => 'Naraki\Core\Controllers\Admin',
        ], call_user_func('static::defaultRouteGroup'));

        $router->group([
            'namespace' => 'Naraki\Core\Controllers\Admin',
            'middleware'=>['admin_auth','admin']
        ], call_user_func('static::broadcasting'));
    }

    public static function broadcasting()
    {
        return function (Router $r) {
            $r->match(
                ['get', 'post'], '/broadcasting/auth',
                'Auth\Broadcast@authenticate'
            );
        };
    }
    public static function defaultRouteGroup()
    {
        return function (Router $r) {
            $r->group([], call_user_func('static::guest'));
            $r->group([
                'middleware' => ['admin_auth'],
            ], call_user_func('static::auth'));
            $r->group([
            ], call_user_func('static::handledBySPA'));
        };
    }

    public static function guest()
    {
        return function (Router $r) {
            $r->get('', 'Admin@index');
            $availableLocales = config('app.locales');
            unset($availableLocales[app()->getLocale()]);
            $availableLocales[''] = '';
            foreach($availableLocales as $locale =>$v){
                $r->post(trans('routes.admin_login', [], $locale), 'Auth\Login@login')->name(self::i18nRouteNames($locale, 'admin.login'));
            }
        };
    }

    public static function handledBySPA(){
        return function (Router $r) {
            $r->get('/{path}', 'Admin@index')->where('path', '(.*)');
        };
    }

    public static function auth()
    {
        return function (Router $r) {
            $r->post('logout', 'Auth\Login@logout');
        };
    }
}