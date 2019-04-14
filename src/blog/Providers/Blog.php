<?php namespace Naraki\Blog\Providers;

use Naraki\Sentry\Models\Person;
use Naraki\Core\EloquentProvider;
use Illuminate\Database\Eloquent\Builder;
use Naraki\Blog\Contracts\Blog as BlogInterface;
use Naraki\Blog\Models\BlogPost;
use Naraki\Blog\Contracts\Category as CategoryInterface;
use Naraki\Blog\Contracts\Tag as TagInterface;
use Naraki\Blog\Contracts\Source as SourceInterface;
use Naraki\Blog\Contracts\Author as AuthorInterface;

class Blog extends EloquentProvider implements BlogInterface
{
    protected $model = \Naraki\Blog\Models\BlogPost::class;

    /**
     * @var \Naraki\Blog\Contracts\Category|\Naraki\Blog\Providers\Category
     */
    private $category;
    /**
     * @var \Naraki\Blog\Contracts\Tag|\Naraki\Blog\Providers\Tag
     */
    private $tag;
    /**
     * @var \Naraki\Blog\Contracts\Source|\Naraki\Blog\Providers\Source $source
     */
    private $source;
    /**
     * @var \Naraki\Blog\Contracts\Author|\Naraki\Blog\Providers\Author $author
     */
    private $author;

    /**
     *
     * @param \Naraki\Blog\Contracts\Category|\Naraki\Blog\Providers\Category $c
     * @param \Naraki\Blog\Contracts\Tag|\Naraki\Blog\Providers\Tag $t
     * @param \Naraki\Blog\Contracts\Source|\Naraki\Blog\Providers\Source $s
     * @param \Naraki\Blog\Contracts\Author|\Naraki\Blog\Providers\Author $a
     */
    public function __construct(CategoryInterface $c, TagInterface $t, SourceInterface $s, AuthorInterface $a)
    {
        parent::__construct();
        $this->category = $c;
        $this->tag = $t;
        $this->source = $s;
        $this->author = $a;
    }

    /**
     * @return \Naraki\Blog\Providers\Blog
     */
    public function blog()
    {
        return $this;
    }

    /**
     * @return \Naraki\Blog\Contracts\Category|\Naraki\Blog\Providers\Category
     */
    public function category(): CategoryInterface
    {
        return $this->category;
    }

    /**
     * @return \Naraki\Blog\Contracts\Tag|\Naraki\Blog\Providers\Tag
     */
    public function tag(): TagInterface
    {
        return $this->tag;
    }

    /**
     * @return \Naraki\Blog\Contracts\Source|\Naraki\Blog\Providers\Source
     */
    public function source(): SourceInterface
    {
        return $this->source;
    }

    /**
     * @return \Naraki\Blog\Contracts\Author|\Naraki\Blog\Providers\Author
     */
    public function author(): AuthorInterface
    {
        return $this->author;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildForDisplay(): Builder
    {
        return $this->buildWithScopes([
            'blog_post_title as title',
            'published_at as date',
            'blog_category_slug as cat',
            'full_name as author',
            'blog_post_is_sticky as featured',
            'entity_types.entity_type_id as type',
            'blog_post_slug as slug',
            'unq as page_views'
        ], [
            'entityType',
            'status',
            'person',
            'category',
            'pageViews'
        ]);
    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildList($attributes): Builder
    {
        return $this->buildWithScopes($attributes, ['person']);
    }

    /**
     * @param string $slug
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildOneBySlug($slug, $attributes = ['*'])
    {
        return $this->buildWithScopes($attributes, ['entityType', 'status', 'person'])
            ->where('blog_post_slug', '=', $slug);
    }

    /**
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model|\Naraki\Sentry\Models\Person $person
     * @return \Naraki\Blog\Models\BlogPost
     */
    public function createOne(array $data, ?Person $person): BlogPost
    {
        if (is_null($person)) {
            throw new \UnexpectedValueException('Person for blog post creation not found.');
        }
        $data['person_id'] = $person->getKey();
        $post = new BlogPost($data);
        $post->save();
        return $post->newQuery()->select([
            'entity_type_id',
            'person_id',
            'language_id',
            'blog_post_id',
            'blog_post_slug',
            'blog_status_id'
        ])->whereKey($post->getKey())->scopes(['entityType'])->first();
    }

    /**
     * @param string $slug
     * @param array $data
     * @return \Naraki\Blog\Models\BlogPost
     */
    public function updateOne($slug, $data)
    {
        $this->build()->where('blog_post_slug', '=', $slug)
            ->update($this->filterFillables($data));
        //using entity_type and language_id to refresh Elasticsearch, post id for categories and tags,
        //status and slug to make the blog post url
        return $this->buildOneWithScopes([
            'entity_type_id',
            'person_id',
            'language_id',
            'blog_post_id',
            'blog_post_slug',
            'blog_status_id'],
            ['entityType'],
            [['blog_post_slug', $data['blog_post_slug'] ?? $slug]])
            ->first();
    }

    /**
     * @param int|array $slug
     * @return mixed
     */
    public function deleteBySlug($slug)
    {
        $model = null;
        if (is_string($slug)) {
            $model = $this->select(['entity_type_id', 'language_id'])
                ->where('blog_post_slug', '=', $slug)
                ->scopes(['entityType'])->get();
            if (!is_null($model)) {
                $this->build()->where('blog_post_slug', '=', $slug)->delete();
            }
        } else {
            $model = $this->build()->select(['entity_type_id', 'language_id'])
                ->whereIn('blog_post_slug', $slug)
                ->scopes(['entityType'])->get();
            if (!is_null($model)) {
                $this->build()->whereIn('blog_post_slug', $slug)->delete();
            }
        }
        return $model;
    }

    /**
     * @param int $n
     * @param string $column
     * @return string
     */
    public function getNth($n, $column)
    {
        return $this->select([$column])
            ->orderBy($column, 'desc')
            ->limit(1)
            ->offset($n)->value($column);
    }

    /**
     * @param int $n
     * @param int $offset
     * @param array $columns
     * @param string $order
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getN($n, $offset, $columns, $order, $direction = 'desc')
    {
        return $this->select($columns)
            ->orderBy($order, $direction)
            ->limit($n)
            ->offset($offset);
    }


}