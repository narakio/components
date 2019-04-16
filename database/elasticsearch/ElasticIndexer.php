<?php

use Naraki\Elasticsearch\Index\Indexer as ElasticSearchIndexer;

class ElasticIndexer extends ElasticSearchIndexer
{
    protected $modelClass = null;

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
        $this->call(BlogPostIndexer::class);
        $this->call(BlogTagIndexer::class);
        $this->call(AuthorIndexer::class);
    }

}