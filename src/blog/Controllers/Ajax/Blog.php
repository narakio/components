<?php namespace Naraki\Blog\Controllers\Ajax;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Blog\Filters\Blog as BlogFilter;
use Naraki\Blog\Job\DeleteElasticsearch;
use Naraki\Blog\Job\UpdateElasticsearch;
use Naraki\Blog\Models\BlogStatus;
use Naraki\Blog\Requests\CreateBlogPost;
use Naraki\Blog\Requests\UpdateBlogPost;
use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Core\Models\Entity;
use Naraki\Media\Facades\Media as MediaRepo;
use Naraki\Media\Models\MediaImgFormat;
use Naraki\Sentry\Facades\User as UserProvider;


class Blog extends Controller
{
    use DispatchesJobs;

    /**
     * @param \Naraki\Blog\Filters\Blog $filter
     * @return array
     */
    public function index(BlogFilter $filter): array
    {
        return [
            'table' => BlogRepo::buildList([
                \DB::raw('null as selected'),
                'blog_post_title',
                'full_name',
                'blog_post_slug'
            ])->filter($filter)->paginate(25),
            'columns' => BlogRepo::createModel()->getColumnInfo([
                'blog_post_title' => (object)[
                    'name' => trans('js-backend.db.blog_post_title'),
                    'width' => '50%'
                ],
                'full_name' => (object)[
                    'name' => trans('js-backend.db.full_name'),
                    'width' => '30%'
                ]
            ], $filter)
        ];
    }

    /**
     * @return array
     */
    public function add(): array
    {
        return [
            'record' => [
                'blog_status' => BlogStatus::getConstantByID(BlogStatus::BLOG_STATUS_DRAFT),
                'blog_post_person' => $this->user->getAttribute('full_name'),
                'person_slug' => $this->user->getAttribute('person_slug'),
                'blog_post_content'=>'',
                'categories' => [],
                'tags' => [],
            ],
            'status_list' => BlogStatus::getConstants('BLOG'),
            'blog_categories' => \Naraki\Blog\Support\Trees\Category::getTree(),
            'thumbnails' => []
        ];
    }

    /**
     * @param $slug
     * @return array
     */
    public function edit($slug): array
    {
        $record = BlogRepo::buildOneBySlug(
            $slug,
            [
                'blog_posts.blog_post_id',
                'blog_post_title',
                'blog_post_slug',
                'blog_post_content',
                'blog_post_excerpt',
                'published_at',
                'blog_posts.blog_status_id',
                'blog_status_name as blog_status',
                'people.full_name as blog_post_person',
                'entity_type_id'
            ])->first();
        if (is_null($record)) {
            return response(trans('error.http.500.blog_post_not_found'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $blogPost = $record->toArray();
        $categories = \Naraki\Blog\Support\Trees\Category::getTreeWithSelected($blogPost['blog_post_id']);
        $blogPost['categories'] = $categories->categories;
        $blogPost['tags'] = BlogRepo::tag()->getByPost($blogPost['blog_post_id']);
        unset($blogPost['entity_type_id'], $blogPost['blog_post_id']);
        return [
            'record' => $blogPost,
            'status_list' => BlogStatus::getConstants('BLOG'),
            'blog_categories' => $categories->tree,
            'url' => $this->getPostUrl($record),
            'source_types' => BlogRepo::source()->listTypes(),
            'sources' => BlogRepo::source()
                ->buildByBlogSlug(
                    $record->getAttribute('blog_post_slug')
                )->get()->toArray(),
            'blog_post_slug' => $record->getAttribute('blog_post_slug'),
            'thumbnails' => MediaRepo::image()->getImages(
                $record->getAttribute('entity_type_id'))
        ];
    }

    /**
     * @param \Naraki\Blog\Requests\CreateBlogPost $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateBlogPost $request)
    {
        try {
            $person = UserProvider::person()->buildOneBySlug(
                $request->getPersonSlug(),
                [UserProvider::person()->getKeyName()]
            )->first();
            $post = BlogRepo::createOne(
                $request->all(),
                $person
            );
            $request->setPersonSlug($person->getKey());
            BlogRepo::category()->attachToPost($request->getCategories(), $post);
            BlogRepo::tag()->attachToPost($request->getTags(), $post);
        } catch (\Exception $e) {
            return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->dispatch(new UpdateElasticsearch(
            UpdateElasticsearch::WRITE_MODE_CREATE,
            $post,
            (object)$request->all(),
            (object)['added' => $request->getTags()],
            is_array($request->getCategories()
            )
        ));
        return response(
            [
                'url' => $this->getPostUrl($post),
                'blog_post_slug' => $post->getAttribute('blog_post_slug'),
            ]);
    }

    /**
     * @param $slug
     * @param \Naraki\Blog\Requests\UpdateBlogPost $request
     * @return array
     */
    public function update($slug, UpdateBlogPost $request): array
    {
        $person = $request->getPersonSlug();

        if (!is_null($person)) {
            $query = UserProvider::person()->buildOneBySlug(
                $person,
                [UserProvider::person()->getKeyName()]
            )->first();
            if (!is_null($query)) {
                $request->setPersonSlug($query->getKey());
            }
        }
        $post = BlogRepo::updateOne($slug, $request->all());
        BlogRepo::category()->updatePost($request->getCategories(), $post);
        $updatedTags = BlogRepo::tag()->updatePost($request->getTags(), $post);
        $this->dispatch(new UpdateElasticsearch(
            UpdateElasticsearch::WRITE_MODE_UPDATE,
            $post,
            (object)$request->all(),
            (object)$updatedTags,
            is_array($request->getCategories()
            )
        ));
        return [
            'url' => $this->getPostUrl($post),
            'blog_post_slug' => $post->getAttribute('blog_post_slug'),
        ];
    }

    /**
     * @param string $slug
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateUrl($slug, Request $request)
    {
        $post = BlogRepo::updateOne($slug, $request->all());
        return response([
            'url' => $this->getPostUrl($post),
        ], 200);
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function destroy($slug): Response
    {
        try {
            $mediaUuids = MediaRepo::image()
                ->getImagesFromSlug(
                    $slug,
                    Entity::BLOG_POSTS,
                    ['media_uuid']
                )->pluck('media_uuid')->all();
            $deleteResult = \DB::transaction(function () use ($slug, $mediaUuids) {
                MediaRepo::image()->delete($mediaUuids, Entity::BLOG_POSTS);
                return BlogRepo::deleteBySlug($slug);
            });
        } catch (\Exception $e) {
            return response(trans('error.http.500.general_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->dispatch(new DeleteElasticsearch($deleteResult));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function batchDestroy(Request $request): Response
    {
        $input = $request->only('posts');
        if (isset($input['posts'])) {
            $postSlugs = $input['posts'];
            $mediaUuids = [];
            foreach ($postSlugs as $slug) {
                $uuids = MediaRepo::image()->getImagesFromSlug($slug, Entity::BLOG_POSTS, ['media_uuid'])
                    ->pluck('media_uuid')->all();
                if (!empty($uuids) && !is_null($uuids)) {
                    $mediaUuids = array_merge($mediaUuids, $uuids);
                }
            }
            $deleteResult = \DB::transaction(function () use ($postSlugs, $mediaUuids) {
                MediaRepo::image()->delete($mediaUuids, Entity::BLOG_POSTS);
                return BlogRepo::deleteBySlug($postSlugs);
            });
            $this->dispatch(new DeleteElasticsearch(
                $deleteResult
            ));
            return response(null, Response::HTTP_NO_CONTENT);
        }
        return response(trans('error.http.500.general_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param string $slug
     * @param string $uuid
     * @return array
     */
    public function setFeaturedImage($slug, $uuid): array
    {
        $entityTypeId = BlogRepo::buildOneBySlug($slug, ['entity_type_id'])
            ->value('entity_type_id');
        MediaRepo::image()->setAsUsed(
            $uuid,
            intval($entityTypeId)
        );
        $media = MediaRepo::image()->getOneSimple($uuid, ['media_extension']);
        if (!is_null($media)) {
            MediaRepo::image()->cropImageToFormat(
                $uuid,
                Entity::BLOG_POSTS,
                \Naraki\Media\Models\Media::IMAGE,
                $media->getAttribute('media_extension'),
                MediaImgFormat::FEATURED
            );
        }
        return MediaRepo::image()
            ->getImagesFromSlug($slug, Entity::BLOG_POSTS, null, $entityTypeId)
            ->toArray();
    }

    /**
     * @param string $slug
     * @param string $uuid
     * @return array
     * @throws \Exception
     */
    public function deleteImage($slug, $uuid): array
    {
        MediaRepo::image()->delete(
            $uuid,
            Entity::BLOG_POSTS);
        return MediaRepo::image()->getImagesFromSlug($slug, Entity::BLOG_POSTS)->toArray();
    }

    /**
     * @param \Naraki\Blog\Models\BlogPost $post
     * @return string
     */
    private function getPostUrl($post): string
    {
        $params = [
            'slug' => $post->getAttribute('blog_post_slug'),
        ];
        if ($post->getAttribute('blog_status_id') != BlogStatus::BLOG_STATUS_PUBLISHED) {
            $params['preview'] = true;
        }
        return route_i18n('blog', $params);
    }

}