<?php namespace Naraki\Sentry\Routes;

use Illuminate\Routing\Router;

class Ajax
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\Sentry\Controllers\Ajax',
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
            $r->get('users', 'User@index')
                ->middleware('can:view,Naraki\Sentry\Models\User');
            $r->get('users/profile', 'User@profile');
            $r->get('user/add', 'User@add')
                ->middleware('can:add,Naraki\Sentry\Models\User');
            $r->post('user/create', 'User@create')
                ->middleware('can:add,Naraki\Sentry\Models\User');
            $r->get('users/{user}', 'User@edit')
                ->middleware('can:edit,Naraki\Sentry\Models\User');
            $r->patch('users/{user}', 'User@update')
                ->middleware('can:delete,Naraki\Sentry\Models\User');
            $r->delete('users/{user}', 'User@destroy')
                ->middleware('can:delete,Naraki\Sentry\Models\User');
            $r->post('users/batch/delete', 'User@batchDestroy')
                ->middleware('can:delete,Naraki\Sentry\Models\User');
            $r->get('users/search/{search}/{limit}', 'User@search')
                ->middleware('can:view,Naraki\Sentry\Models\User');
            $r->get('people/search/{search}/{limit}', 'Person@search')
                ->middleware('can:view,Naraki\Sentry\Models\User');

            $r->get('groups', 'Group@index')
                ->middleware('can:view,Naraki\Sentry\Models\Group');
            $r->get('groups/create', 'Group@add')
                ->middleware('can:add,Naraki\Sentry\Models\Group');
            $r->post('groups', 'Group@create')
                ->middleware('can:add,Naraki\Sentry\Models\Group');
            $r->get('groups/{group}', 'Group@edit')
                ->middleware('can:edit,Naraki\Sentry\Models\Group');
            $r->patch('groups/{group}', 'Group@update')
                ->middleware('can:edit,Naraki\Sentry\Models\Group');
            $r->delete('groups/{group}', 'Group@destroy')
                ->middleware('can:delete,Naraki\Sentry\Models\Group');

            $r->get('members/{group}/search/{search}/{limit}', 'GroupMember@search')
                ->middleware('can:view,Naraki\Sentry\Models\Group');
            $r->get('members/{group}', 'GroupMember@index')
                ->middleware('can:view,Naraki\Sentry\Models\Group');
            $r->patch('members/{group}', 'GroupMember@update')
                ->middleware('can:view,Naraki\Sentry\Models\Group');
        };
    }
}