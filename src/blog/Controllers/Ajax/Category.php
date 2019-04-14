<?php namespace Naraki\Blog\Controllers\Ajax;

use Naraki\Core\Controllers\Admin\Controller;
use Illuminate\Http\Response;
use Naraki\Blog\Facades\Blog;

class Category extends Controller
{
    public function index()
    {
        return \Naraki\Blog\Support\Trees\Category::getTree();
    }

    /**
     * @return \Illuminate\Http\Response|array
     */
    public function create()
    {
        $request = app('request');
        $parent = $request->get('parent');
        $label = $request->get('label');
        if (is_null($label)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        $newCat = Blog::category()->createOne($label, $parent);
        if (is_null($newCat)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }

        return ['id' => $newCat->getAttribute('blog_category_slug')];
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\Response|array
     */
    public function update($id)
    {
        $cat = Blog::category()->getCat($id);
        if (!is_null($cat)) {
            $label = app('request')->get('label');
            $cat->setAttribute('blog_category_name', $label);
            $cat->setAttribute('blog_category_slug', slugify($label, '-', app()->getLocale()));
            $cat->save();
        }
        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function delete($id)
    {
        $model = Blog::category()->build()
            ->where('blog_category_slug', $id)->first();
        if (!is_null($model)) {
            $model->delete();
        }
        return response(null, Response::HTTP_NO_CONTENT);
    }
}