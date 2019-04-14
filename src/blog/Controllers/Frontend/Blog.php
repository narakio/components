<?php namespace Naraki\Blog\Controllers\Frontend;

use Naraki\Core\Support\Viewable\Jobs\ProcessPageView;
use Naraki\Core\Models\Language;
use Naraki\Core\Support\Frontend\Breadcrumbs;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Naraki\Blog\Facades\Blog as BlogFacade;
use Naraki\Media\Facades\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Blog extends Controller
{
    use DispatchesJobs;

    /**
     * @var \Naraki\Blog\Contracts\Blog|\Naraki\Blog\Providers\Blog
     */
    private $blogRepo;

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPost($slug)
    {
        $post = BlogFacade::buildOneBySlug($slug, [
            'blog_post_title as title',
            'blog_post_content as content',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'entity_types.entity_type_id',
            'person_slug as author',
            'full_name as person',
            'unq as page_views',
            'language_id as language',
            'blog_posts.updated_at as date_modified',
            'published_at as date_published'
        ])->scopes(['pageViews'])->first();
        if (is_null($post)) {
            throw new NotFoundHttpException('Blog Post not found');
        }
        $dbImages = Media::image()->getImages(
            $post->getAttribute('entity_type_id'), [
                'media_in_use as featured',
                'media_uuid as uuid',
                'media_extension as ext',
                'entity_types.entity_type_id as type',
                'entity_id'
            ]
        );
        $categories = BlogFacade::buildOneBySlug($slug, ['blog_category_slug as cat'])
            ->scopes(['category'])
            ->orderBy('blog_category_id', 'asc')
            ->get();
        $firstCategory = $categories->first();
        $tags = BlogFacade::buildOneBySlug($slug, ['blog_tag_slug as tag', 'blog_tag_name as name'])
            ->scopes(['tag'])
            ->get();

        $sources = BlogFacade::source()->buildByBlogSlug($slug)->get();
        $otherPosts = $otherPostMedia = [];
        if (!is_null($firstCategory)) {
            $otherPosts = BlogFacade::buildWithScopes([
                'blog_post_title as title',
                'blog_post_slug as slug',
                'published_at as date',
                'entity_types.entity_type_id as type',
                'person_slug as author',
                'full_name as person',
                'unq as page_views'
            ], ['entityType', 'pageViews', 'person', 'language', 'category' => $firstCategory->getAttribute('cat')])
                ->where('blog_post_slug', '!=', $slug)
                ->orderBy('published_at', 'desc')->limit(4)->get();
            $otherPostMedia = $this->getImages($otherPosts);
        }

        $media = null;
        foreach ($dbImages as $image) {
            if ($image->featured == 1) {
                $media = $image;
            }
        }

        $breadcrumbs = [];
        foreach ($categories as $cat) {
            $breadcrumbs[] = [
                'label' => trans(sprintf('pages.blog.category.%s', $cat->getAttribute('cat'))),
                'url' => route_i18n('blog.category', $cat->getAttribute('cat'))
            ];
        }
        $this->dispatch(new ProcessPageView($post));
        return view('blog::post', compact(
                'post', 'breadcrumbs', 'media', 'categories',
                'tags', 'otherPosts', 'otherPostMedia', 'sources')
        );
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function category($slug)
    {
        $posts = BlogFacade::buildWithScopes([
            'blog_post_title as title',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'blog_category_slug as cat',
            'entity_types.entity_type_id as type',
            'person_slug as author',
        ], ['entityType', 'person', 'language', 'category' => $slug])
            ->orderBy('published_at', 'desc')
            ->where('language_id', Language::getAppLanguageId())
            ->limit(5)
            ->get();
        if (is_null($posts)) {
            throw new NotFoundHttpException('Blog Category not found');
        }

        $media = $this->getImages($posts);

        $featured = $posts->shift();
        $mvps = $this->getMostViewedPosts($slug);
        $mvpImages = $this->getImages(clone($mvps));

        $breadcrumbs = Breadcrumbs::render([
            [
                'label' => trans(sprintf('pages.blog.category.%s', $slug)),
            ]
        ]);
        $title = trans('titles.routes.blog.category', ['name' => $featured->getAttribute('cat')]);

        return view(
            'blog::category',
            compact('breadcrumbs', 'posts', 'media', 'featured', 'mvps', 'mvpImages', 'title')
        );
    }

    /**
     * @param string $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tag($slug)
    {
        $posts = BlogFacade::buildWithScopes([
            'blog_post_title as title',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'entity_types.entity_type_id as type',
            'person_slug as author',
            'blog_tag_name as tag'
        ], ['entityType', 'person', 'tag' => $slug])
            ->orderBy('published_at', 'desc')
            ->where('language_id', Language::getAppLanguageId())
            ->limit(8)
            ->get();
        if (is_null($posts)) {
            throw new NotFoundHttpException('Blog Tag not found');
        }
        $tag = (object)$posts->first()->only(['tag']);
        $media = $this->getImages($posts);
        $title = trans('titles.routes.blog.tag', ['name' => $tag->tag]);
        return view(
            'blog::tag', compact('posts', 'media', 'tag', 'title'));
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function author($slug)
    {
        $posts = BlogFacade::buildWithScopes([
            'blog_post_title as title',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'blog_category_slug as cat',
            'entity_types.entity_type_id as type',
            'full_name as author'
        ], ['entityType', 'person' => $slug, 'language', 'category'])
            ->where('parent_id', null)
            ->orderBy('published_at', 'desc')
            ->limit(8)
            ->get();
        if ($posts->isEmpty()) {
            throw new HttpException(404, 'Author not found');
        }
        $author = (object)$posts->first()->only(['author']);
        if (is_null($posts)) {
            throw new NotFoundHttpException('Author not found');
        }
        $media = $this->getImages($posts);
        $title = trans('titles.routes.blog.author', ['name' => $author->author]);
        return view(
            'blog::author', compact('posts', 'media', 'author', 'title')
        );
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return array
     */
    private function getImages($collection)
    {
        $dbImages = Media::image()->getImages(
            $collection->pluck('type')->all(), [
                'media_uuid as uuid',
                'media_extension as ext',
                'entity_types.entity_type_id as type',
                'entity_types.entity_id as entity_id'
            ]
        );
        $media = [];
        foreach ($dbImages as $image) {
            $media[$image->type] = $image;
        }
        return $media;
    }

    /**
     * @param $slug
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getMostViewedPosts($slug)
    {
        return BlogFacade::buildWithScopes([
            'blog_post_title as title',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'blog_category_slug as cat',
            'entity_types.entity_type_id as type',
            'person_slug as author',
            'unq as page_views'
        ], ['entityType', 'person', 'category' => $slug, 'language', 'pageViews'])
            ->orderBy('page_views', 'desc')->limit(10)->get();
    }

}