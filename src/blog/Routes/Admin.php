<?php namespace Naraki\Blog\Routes;

use Illuminate\Routing\Router;

class Admin
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'Naraki\Blog\Controllers\Ajax',
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
            $r->get('blog/categories', 'Category@index')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->post('blog/categories', 'Category@create')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->patch('blog/categories/{id}', 'Category@update')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->delete('blog/categories/{id}', 'Category@delete')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');

            $r->get('blog/posts', 'Blog@index')
                ->middleware('can:view,Naraki\Blog\Models\BlogPost');
            $r->get('blog/post/create', 'Blog@add')
                ->middleware('can:add,Naraki\Blog\Models\BlogPost');
            $r->post('blog/post/create', 'Blog@create')
                ->middleware('can:add,Naraki\Blog\Models\BlogPost');
            $r->get('blog/post/edit/{slug}', 'Blog@edit')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->post('blog/post/url/edit/{slug}', 'Blog@updateUrl');
            $r->post('blog/post/edit/{slug}', 'Blog@update')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->delete('blog/post/{slug}', 'Blog@destroy');
            $r->post('blog/post/batch/delete', 'Blog@batchDestroy');
            $r->post('blog/post/source/create', 'Source@create');
            $r->delete('blog/post/source/delete/{id}/{slug}', 'Source@destroy');

            $r->patch('blog/post/edit/{slug}/image/{uuid}', 'Blog@setFeaturedImage')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');
            $r->delete('blog/post/edit/{slug}/image/{uuid}', 'Blog@deleteImage')
                ->middleware('can:edit,Naraki\Blog\Models\BlogPost');

        };
    }
}