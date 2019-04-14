<?php namespace Naraki\Sentry\Jobs;

use Naraki\Core\Job;
use Naraki\Elasticsearch\Facades\ElasticSearchIndex;
use Naraki\Media\Models\Media;
use Naraki\Media\Models\MediaImgFormat;

class UpdateUserElasticsearch extends Job
{
    const WRITE_MODE_CREATE = 1;
    const WRITE_MODE_UPDATE = 2;

    public $queue = 'db';
    /**
     * @var int
     */
    private $writeMode;

    /**
     * @var \stdClass
     */
    public $documentContents = [];
    /**
     * @var int
     */
    private $userId;


    /**
     *
     * @param int $writeMode
     * @param int $userId
     */
    public function __construct(
        $writeMode,
        $userId
    ) {
        $this->writeMode = $writeMode;
        $this->userId = $userId;
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
            $dbUser = \Naraki\Sentry\Models\User::query()
                ->select([
                    'entity_type_id as id',
                    'username',
                    'email',
                    'full_name'
                ])
                ->scopes(['entityType'])
                ->where('users.user_id', $this->userId)
                ->first();
            if (!is_null($dbUser)) {
                $this->documentContents = $dbUser->toArray();

                $dbImage = \Naraki\Media\Models\MediaEntity::buildImages(null, [
                    'media_uuid as uuid',
                    'media_extension as ext',
                    'entity_types.entity_type_id as id',
                    'entity_id'
                ])->where('entity_types.entity_id', \Naraki\Core\Models\Entity::USERS)
                    ->where('media_in_use', '1')
                    ->where('entity_types.entity_type_id', $dbUser->getAttribute('id'))
                    ->first();
                if (!is_null($dbImage)) {
                    $this->documentContents['avatar'] = $dbImage->present('thumbnail',
                        [Media::IMAGE_AVATAR, MediaImgFormat::ORIGINAL]);
                }
            }

            if (!empty($this->documentContents)) {
                $id = $this->documentContents['id'];
                unset($this->documentContents['id']);
                $document = [
                    'index' => 'naraki.users.en',
                    'type' => 'main',
                    'id' => $id,
                ];

                switch ($this->writeMode) {
                    case self::WRITE_MODE_CREATE:
                        $document['body'] = $this->documentContents;
                        ElasticSearchIndex::index($document);
                        break;
                    case self::WRITE_MODE_UPDATE:
                        $document['body'] = [
                            'doc' => $this->documentContents
                        ];
                        ElasticSearchIndex::update($document);
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

}