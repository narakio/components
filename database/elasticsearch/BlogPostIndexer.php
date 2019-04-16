<?php

use Naraki\Elasticsearch\Index\Indexer as ElasticSearchIndexer;
use Naraki\Elasticsearch\Index\Seeder;

class BlogPostIndexer extends ElasticSearchIndexer
{
    /**
     * Full name of the model that should be mapped
     *
     * @var \Naraki\Blog\Models\BlogPost
     */
    protected $modelClass = Naraki\Blog\Models\BlogPost::class;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the mapping.
     *
     * @return void
     */
    public function run()
    {
        $this->down();
        $this->up();
        $this->indexData($this->prepareData());
    }

    private function indexData($data)
    {
        foreach ($data as $lang => $posts) {
            $seeder = new Seeder(sprintf('%s.%s', $this->getIndexName(), $lang));
            $seeder->bulk($posts);
        }
    }

    private function prepareData()
    {
        $limit = 10;

        $dbImages = \Naraki\Media\Models\MediaEntity::buildImages(null, [
            'media_uuid as uuid',
            'media_extension as ext',
            'entity_types.entity_type_id as type',
            'entity_id'
        ])->where('entity_types.entity_id', \Naraki\Core\Models\Entity::BLOG_POSTS)
            ->where('media_in_use', '1')
//            ->limit($limit)
            ->get();
        $images = [];
        foreach ($dbImages as $image) {
            $images[$image->getAttribute('type')] = $image->present('thumbnail');
        }

        $dbPosts = \Naraki\Blog\Models\BlogPost::query()
            ->scopes(['entityType', 'person'])
            ->select([
                'blog_posts.blog_post_id as id',
                'entity_types.entity_type_id as type',
                'blog_post_excerpt as excerpt',
                'blog_post_content as content',
                'blog_post_slug as slug',
                'blog_post_title as title',
                'full_name as person',
                'person_slug as author',
                'blog_posts.published_at as published',
                'language_id as lang'
            ])
            ->where('blog_status_id', \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_PUBLISHED)
//            ->limit($limit)
            ->get();

        $posts = [];
        $postIds = [];
        $languageIds = [];

        foreach ($dbPosts as $post) {
            $index = $post->getAttribute('type');
            $postIds[$post->getAttribute('id')] = $index;
            $languageIds[$index] = $post->getAttribute('lang');
            $posts[$index] = [
                'title' => $post->getAttribute('title'),
                'content' => $post->getAttribute('content'),
                'date' => $post->getAttribute('published'),
                'meta' => [
                    'url' => route_i18n('blog', ['slug' => $post->getAttribute('slug')]),
                    'excerpt' => $post->getAttribute('excerpt'),
                    'author' => [
                        'name' => $post->getAttribute('person'),
                        'url' => route_i18n('blog.author', ['slug' => $post->getAttribute('author')]),
                    ]
                ]
            ];
            if (isset($images[$index])) {
                $posts[$index]['meta']['image'] = $images[$index];
            } else {
                $posts[$index]['meta']['image'] = null;
            }
        }
        unset($dbPosts, $dbImages, $images);

        $dbCategories = \Naraki\Blog\Facades\Blog::buildWithScopes([
            'blog_posts.blog_post_id as id',
            'blog_category_name as name',
            'blog_category_slug as slug',
        ], ['category'])
            ->where('blog_status_id', \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_PUBLISHED)
//            ->limit($limit)
            ->get();

        $categoryHierarchies = [];
        foreach ($dbCategories as $category) {
            if (!isset($postIds[$category->getAttribute('id')])) {
                continue;
            }
            $index = $postIds[$category->getAttribute('id')];
            if (!isset($categoryHierarchies[$category->getAttribute('slug')])) {
                $categoryHierarchies[$category->getAttribute('slug')] = [];
                $tmp = \Naraki\Blog\Facades\Blog::category()->getHierarchy($category->getAttribute('slug'));
                foreach ($tmp as $mp) {
                    $categoryHierarchies[$category->getAttribute('slug')][] = [
                        'name' => $mp->label,
                        'url' => route_i18n('blog.category', ['slug' => $mp->id]),
                    ];
                }
            }

            if (!isset($posts[$index]['meta']['category'])) {
                $posts[$index]['meta']['category'] = [];
            }
            $posts[$index]['meta']['category'][] = $categoryHierarchies[$category->getAttribute('slug')];
        }
        unset($dbCategories);

        $dbTags = \Naraki\Blog\Models\BlogPost::query()
            ->select([
                'blog_posts.blog_post_id as id',
                'blog_tag_name as name',
                'blog_tag_slug as slug'
            ])
            ->scopes(['tag'])
            ->where('blog_status_id', \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_PUBLISHED)
            ->orderBy('blog_posts.blog_post_id', 'asc')
//            ->limit($limit)
            ->get();

        foreach ($dbTags as $tag) {
            if (!isset($postIds[$tag->getAttribute('id')])) {
                continue;
            }
            $index = $postIds[$tag->getAttribute('id')];
            if (!isset($posts[$index]['meta']['tag'])) {
                $posts[$index]['meta']['tag'] = [];
            }
            $posts[$index]['meta']['tag'][] = [
                'name' => $tag->getAttribute('name'),
                'url' => route_i18n('blog.tag', ['slug' => $tag->getAttribute('slug')]),
            ];
        }
        unset($dbTags);

        $langPosts = ['fr' => [], 'en' => []];
        foreach ($languageIds as $typeId => $item) {
            if (intval($item) == 1) {
                $langPosts['en'][$typeId] = $posts[$typeId];
            } else {
                $langPosts['fr'][$typeId] = $posts[$typeId];
            }
        }
        unset($posts);
        return $langPosts;
    }

    private function down()
    {
        Seeder::delete(sprintf('%s.%s', $this->getIndexName(), 'en'));
        Seeder::delete(sprintf('%s.%s', $this->getIndexName(), 'fr'));
    }

    private function up()
    {
        $mapping = [
            'title' => [
                'type' => 'text',
                'analyzer' => 'std_stop_en',
                'search_analyzer' => 'std_stop_en',
                'search_quote_analyzer' => 'standard'
            ],
            'content' => [
                'type' => 'text',
                'analyzer' => 'std_strip_en',
                'search_analyzer' => 'std_strip_en',
                'search_quote_analyzer' => 'standard'
            ],
            'date' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ],
            'meta' => [
                'enabled' => false
            ],
        ];
        $source = ['includes' => ['title', 'meta', 'date']];

        $indexEn = new \Naraki\Elasticsearch\Index\Mapping(
            sprintf('%s.%s', $this->getIndexName(), 'en'),
            $mapping,
            $source
        );
        Seeder::insert($indexEn->toArray());

        $mapping['title']['analyzer'] = 'std_stop_fr';
        $mapping['title']['search_analyzer'] = 'std_stop_fr';
        $mapping['content']['analyzer'] = 'std_strip_fr';
        $mapping['content']['search_analyzer'] = 'std_strip_fr';

        $indexFr = new \Naraki\Elasticsearch\Index\Mapping(
            sprintf('%s.%s', $this->getIndexName(), 'fr'),
            $mapping,
            $source
        );
        Seeder::insert($indexFr->toArray());
    }
}
