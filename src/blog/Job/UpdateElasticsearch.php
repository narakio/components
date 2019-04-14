<?php namespace Naraki\Blog\Job;

use Naraki\Core\Job;
use Naraki\Sentry\Models\Person;
use Naraki\Blog\Facades\Blog;
use Naraki\Blog\Models\BlogPost;
use Naraki\Blog\Models\BlogTag;
use Naraki\Elasticsearch\Facades\ElasticSearchIndex;

class UpdateElasticsearch extends Job
{
    const WRITE_MODE_CREATE = 1;
    const WRITE_MODE_UPDATE = 2;
    public $queue = 'db';
    /**
     * @var int
     */
    private $writeMode;
    /**
     * @var \Naraki\Blog\Models\BlogPost
     */
    private $blogPostData;
    /**
     * @var \stdClass
     */
    private $requestData;
    private $refreshCategories = false;
    /**
     * @var \stdClass
     */
    private $tags;
    private $documentContents = [];
    private $refreshedFields = [
        'blog_post_title' => ['title', 'meta' => false],
        'blog_post_content' => ['content', 'meta' => false],
        'blog_post_excerpt' => ['excerpt', 'meta' => true],
        'blog_post_slug' => ['url', 'meta' => false],
        'tag' => ['tag', 'meta' => true],
        'url' => ['url', 'meta' => true],
        'author' => ['author', 'meta' => true],
        'category' => ['category', 'meta' => true],
        'image' => ['image', 'meta' => true],
        'published_at' => ['date', 'meta' => false]
    ];

    /**
     *
     * @param int $writeMode
     * @param \Naraki\Blog\Models\BlogPost $blogPostData
     * @param \stdClass $requestData
     * @param \stdClass $tags
     * @param bool $refreshCategories
     */
    public function __construct(
        int $writeMode,
        BlogPost $blogPostData,
        \stdClass $requestData = null,
        \stdClass $tags = null,
        bool $refreshCategories = null
    ) {
        $this->writeMode = $writeMode;
        $this->blogPostData = $blogPostData;
        $this->requestData = $requestData;
        $this->refreshCategories = $refreshCategories;
        $this->tags = $tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!config('elastic-search.enabled')) {
            $this->delete();
        }

        parent::handle();
        try {
            $this->processPost();

            if (isset($this->requestData->person_id)) {
                $this->processAuthor();
            }

            if ($this->refreshCategories === true) {
                $this->processCategories();
            }

            if (!is_null($this->tags)) {
                $this->processTags();
            }

            if (!empty($this->documentContents)) {
                switch ($this->writeMode) {
                    case self::WRITE_MODE_CREATE:
                        $this->createDocument();
                        break;
                    case self::WRITE_MODE_UPDATE:
                        $this->updateDocument();
                        break;
                }
            }
            $this->delete();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(),
                ['trace' => $e->getTraceAsString(), 'document' => $this->documentContents]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
            $this->release(60);
        }
    }

    public function createDocument()
    {
        $model = new BlogPost();
        $this->documentContents['meta']['url'] = route_i18n(
            'blog',
            ['slug' => $this->blogPostData->getAttribute('blog_post_slug')]
        );
        $document = [
            'index' => $model->getLocaleDocumentIndex($this->blogPostData->getAttribute('language_id')),
            'type' => 'main',
            'id' => $this->blogPostData->getAttribute('entity_type_id'),
            'body' => $this->documentContents
        ];

        ElasticSearchIndex::index($document);
    }

    public function updateDocument()
    {
        $model = new BlogPost();
        $document = [
            'index' => $model->getLocaleDocumentIndex($this->blogPostData->getAttribute('language_id')),
            'type' => 'main',
            'id' => intval($this->blogPostData->getAttribute('entity_type_id')),
            'body' => [
                'doc' => $this->documentContents
            ]
        ];
        ElasticSearchIndex::update($document);
    }

    private function processPost()
    {
        foreach ($this->refreshedFields as $field => $esField) {
            if (isset($this->requestData->{$field})) {
                if ($esField['meta']) {
                    $this->documentContents['meta'][$esField[0]] = $this->requestData->{$field};
                } else {
                    $this->documentContents[$esField[0]] = $this->requestData->{$field};
                }
            } else {
                if ($this->writeMode === self::WRITE_MODE_CREATE) {
                    if ($esField['meta']) {
                        $this->documentContents['meta'][$esField[0]] = null;
                    } else {
                        $this->documentContents[$esField[0]] = null;
                    }
                }
            }
        }
    }

    private function processAuthor()
    {
        $person = Person::query()->select(['full_name', 'person_slug'])
            ->whereKey($this->requestData->person_id)->first();
        if (!is_null($person)) {
            if (!isset($this->documentContents['meta'])) {
                $this->documentContents['meta'] = [];
            }
            $this->documentContents['meta']['author'] = [
                'name' => $person->getAttribute('full_name'),
                'url' => route_i18n('blog.author', ['slug' => $person->getAttribute('person_slug')])
            ];
        }
    }

    private function processTags()
    {
        if (!empty($this->tags->added)) {
            $tags = Blog::tag()->getByPostColumns($this->blogPostData->getAttribute('blog_post_id'));
            if (!isset($this->documentContents['meta'])) {
                $this->documentContents['meta'] = [];
            }
            $model = new BlogTag();
            foreach ($tags as $tag) {
                $tagData = [
                    'name' => $tag->getAttribute('name'),
                    'url' => route_i18n('blog.tag', ['slug' => $tag->getAttribute('slug')]),
                ];
                $this->documentContents['meta']['tag'][] = $tagData;
                ElasticSearchIndex::index([
                    'index' => $model->getLocaleDocumentIndex(
                        $this->blogPostData->getAttribute('language_id')
                    ),
                    'type' => 'main',
                    'id' => $tag->getAttribute('id'),
                    'body' => $tagData
                ]);
            }
        }

        if (isset($this->tags->removed) && !empty($this->tags->removed)) {
            $model = new BlogTag();
            foreach ($this->tags->removed as $removed) {
                ElasticSearchIndex::destroy(
                    [
                        'index' => $model->getLocaleDocumentIndex(
                            $this->blogPostData->getAttribute('language_id')
                        ),
                        'type' => 'main',
                        'id' => $removed,
                    ]
                );
            }
        }
    }

    private function processCategories()
    {
        $cat = Blog::buildWithScopes([
            'blog_category_name as name',
            'blog_category_slug as slug',
        ], ['category'])
            ->where(Blog::getQualifiedKeyName(), $this->blogPostData->getAttribute('blog_post_id'))
            ->first();
        if (!is_null($cat)) {
            $tmp = $cat->toArray();
            $cats = Blog::category()->getHierarchy($tmp['slug']);
            $categories = [];
            foreach ($cats as $mp) {
                $categories[] = [
                    'name' => $mp->label,
                    'url' => route_i18n('blog.category', ['slug' => $mp->id]),
                ];
            }
            if (!isset($this->documentContents['meta'])) {
                $this->documentContents['meta'] = [];
            }
            $this->documentContents['meta']['category'] = $categories;
        }
    }

    public function __get($value)
    {
        return $this->{$value};
    }
}