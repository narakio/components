<?php namespace Naraki\Core\Routes;

use Illuminate\Routing\Router;

class Ajax
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\Core\Controllers\Ajax',
        ],
            function (Router $r) {
                $r->group([
                    'middleware' => ['admin_auth','admin']
                ], call_user_func('static::routes'));
            }
        );
    }

    public static function routes()
    {
        return function (Router $r) {
            $r->get('dashboard', 'Dashboard@index');
        };
    }
}