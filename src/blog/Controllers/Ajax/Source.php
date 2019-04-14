<?php namespace Naraki\Blog\Controllers\Ajax;

use Naraki\Blog\Contracts\Source as BlogSourceProvider;
use Naraki\Core\Controllers\Admin\Controller;
use Illuminate\Http\Response;

class Source extends Controller
{
    /**
     * @param \Naraki\Blog\Contracts\Source|\Naraki\Blog\Providers\Source $sourceRepo
     * @return \Illuminate\Http\Response|array
     */
    public function create(BlogSourceProvider $sourceRepo)
    {
        $type = intval(app('request')->get('type'));
        $content = app('request')->get('content');
        $slug = app('request')->get('blog_slug');

        if (is_null($content) || !\Naraki\Blog\Models\BlogSource::isValidValue($type)) {
            return response([$type, $content, $slug], Response::HTTP_NO_CONTENT);
        }

        $sourceRepo->createOne($type, $content, $slug);

        return response(
            ['sources' => $sourceRepo->buildByBlogSlug($slug)->get()->toArray()],
            Response::HTTP_OK
        );
    }

    /**
     * @param string $id
     * @param string $slug
     * @param \Naraki\Blog\Contracts\Source|\Naraki\Blog\Providers\Source $sourceRepo
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function destroy($id,$slug, BlogSourceProvider $sourceRepo)
    {
        $sourceRepo->deleteOne(intval($id));
        return response(
            ['sources' => $sourceRepo->buildByBlogSlug($slug)->get()->toArray()],
            Response::HTTP_OK
        );
    }
}