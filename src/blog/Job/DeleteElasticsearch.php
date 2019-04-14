<?php namespace Naraki\Blog\Job;

use Naraki\Core\Job;
use Illuminate\Support\Collection;
use Naraki\Blog\Models\BlogPost;
use Naraki\Elasticsearch\Facades\ElasticSearchIndex;

class DeleteElasticsearch extends Job
{
    public $queue = 'db';

    /**
     * @var BlogPost[]
     */
    private $blogPostData;

    /**
     *
     * @param \Illuminate\Support\Collection $blogPostData
     */
    public function __construct(
        Collection $blogPostData
    ) {
        $this->blogPostData = $blogPostData;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();
        try {
            $this->deleteDocument();
            $this->delete();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
            $this->release(60);
        }
    }

    public function deleteDocument()
    {
        $model = new BlogPost();
        foreach ($this->blogPostData as $data) {
            ElasticSearchIndex::destroy(
                [
                    'index' => $model->getLocaleDocumentIndex($data->getAttribute('language_id')),
                    'type' => 'main',
                    'id' => $data->getAttribute('entity_type_id'),
                ]
            );
        }
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}