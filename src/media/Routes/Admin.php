<?php namespace Naraki\Media\Routes;

use Illuminate\Routing\Router;

class Admin
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\Media\Controllers',
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
            $r->get('media/{entity}/{media}', 'Media@edit')
                ->middleware('can:view,Naraki\Media\Models\MediaEntity');
            $r->get('media', 'Media@index')
                ->middleware('can:view,Naraki\Media\Models\MediaEntity');
            $r->patch('media/{media}', 'Media@update')
                ->middleware('can:edit,Naraki\Media\Models\MediaEntity');
            $r->post('media/add', 'Media@add')
                ->middleware('can:add,Naraki\Media\Models\MediaEntity');
            $r->post('media/crop/avatar', 'Media@cropAvatar');
            $r->post('media/crop/image', 'Media@crop');
        };
    }
}