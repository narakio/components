<?php

use Naraki\Elasticsearch\Index\Indexer as ElasticSearchIndexer;
use Naraki\Elasticsearch\Index\Seeder;

class AuthorIndexer extends ElasticSearchIndexer
{
    /**
     * Full name of the model that should be mapped
     *
     * @var \Naraki\Blog\Models\BlogPost
     */
    protected $modelClass = \Naraki\Sentry\Models\Person::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function getIndexName()
    {
        return strtolower(sprintf('%s.blog_authors', config('app.name')));
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
        $dbAuthor = \Naraki\Blog\Models\BlogPost::query()
            ->select([
                'language_id as lang',
                'full_name as person',
                'person_slug as author',
            ])
            ->person()
            ->where('blog_status_id', \Naraki\Blog\Models\BlogStatus::BLOG_STATUS_PUBLISHED)
//            ->limit($limit)
            ->get();

        $authors = [];
        foreach ($dbAuthor as $author) {
            $lang = $author->getAttribute('lang');
            if (!isset($authors[$author->getAttribute('lang')])) {
                $authors[$lang] = [];
            }

            $name = $author->getAttribute('person');
            if (!isset($authors[$lang][$name])) {
                $authors[$lang][$name] = [
                    'name' => $author->getAttribute('person'),
                    'url' => route_i18n('blog.author', ['slug' => $author->getAttribute('author')]),
                ];
            }
        }
        unset($dbAuthor);

        return [
            'en' => array_values($authors[\Naraki\Core\Models\Language::DB_LANGUAGE_ENGLISH_ID]),
            'fr' => array_values($authors[\Naraki\Core\Models\Language::DB_LANGUAGE_FRENCH_ID])
        ];
    }

    private function down()
    {
        Seeder::delete(sprintf('%s.%s', $this->getIndexName(), 'en'));
        Seeder::delete(sprintf('%s.%s', $this->getIndexName(), 'fr'));
    }

    private function up()
    {
        $mapping = [
            'name' => [
                'type' => 'text',
            ],
            'url' => [
                'enabled' => false
            ],
        ];

        $indexEn = new \Naraki\Elasticsearch\Index\Mapping(
            sprintf('%s.%s', $this->getIndexName(), 'en'),
            $mapping
        );
        Seeder::insert($indexEn->toArray());

        $indexFr = new \Naraki\Elasticsearch\Index\Mapping(
            sprintf('%s.%s', $this->getIndexName(), 'fr'),
            $mapping
        );
        Seeder::insert($indexFr->toArray());
    }

}
