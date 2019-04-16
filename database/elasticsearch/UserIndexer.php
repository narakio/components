<?php

use Naraki\Elasticsearch\Index\Seeder;
use Naraki\Elasticsearch\Index\Mapping;
use Naraki\Elasticsearch\Index\Indexer as ElasticSearchIndexer;
use Naraki\Media\Models\Media;
use Naraki\Media\Models\MediaImgFormat;

class UserIndexer extends ElasticSearchIndexer
{

    /**
     * Full name of the model that should be mapped
     *
     * @var \Naraki\Blog\Models\BlogPost
     */
    protected $modelClass = \Naraki\Sentry\Models\User::class;

    public function __construct()
    {
        parent::__construct();
    }

    public function getIndexName()
    {
        return strtolower(sprintf('%s.users', config('app.name')));
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
        $seeder = new Seeder(sprintf('%s.en', $this->getIndexName()));
        $seeder->bulk($data);
    }

    private function prepareData()
    {
        $dbUser = \Naraki\Sentry\Models\User::query()
            ->select([
                'entity_type_id as id',
                'username',
                'email',
                'full_name'
            ])
            ->scopes(['entityType'])
            ->where('users.user_id', '>', 1)
            ->get();

        $dbImages = \Naraki\Media\Models\MediaEntity::buildImages(null, [
            'media_uuid as uuid',
            'media_extension as ext',
            'entity_types.entity_type_id as id',
            'entity_id'
        ])->where('entity_types.entity_id', \Naraki\Core\Models\Entity::USERS)
            ->where('media_in_use', '1')
            ->get();
        $images = [];
        foreach ($dbImages as $image) {
            $images[$image->getAttribute('id')] = $image->present('thumbnail',
                [Media::IMAGE_AVATAR, MediaImgFormat::ORIGINAL]);
        }

        $users = [];
        foreach ($dbUser as $v) {
            $val = $v->toArray();
            if (isset($images[$val['id']])) {
                $val['avatar'] = $images[$val['id']];
            }
            unset($val['id']);
            $users[$v->getAttribute('id')] = $val;
        }
        return $users;

    }

    private function down()
    {
        Seeder::delete(sprintf('%s.%s', $this->getIndexName(), 'en'));
    }

    private function up()
    {
        //For username searches, we have the suggester by default when we query against the "username" field
        //or an exact search when we query against the field "username.exact"
        $mapping = [
            'username' => [
                'type' => 'text',
                'analyzer' => 'std_autocomplete_slug',
                'search_analyzer' => 'standard',
                'fields' => [
                    'exact' => ['type' => 'keyword']
                ]
            ],
            'email' => [
                'type' => 'keyword',
            ],
            'full_name' => [
                'type' => 'text',
                'analyzer' => 'std_autocomplete_string',
                'search_analyzer' => 'standard'
            ],
            'avatar' => [
                'type' => 'object',
                'enabled' => false
            ]
        ];
        $indexEn = new Mapping(
            sprintf('%s.%s', $this->getIndexName(), 'en'),
            $mapping
        );
        Seeder::insert($indexEn->toArray());
    }

}