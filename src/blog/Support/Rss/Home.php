<?php namespace Naraki\Blog\Support\Rss;

use Naraki\Core\Models\Language;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Media\Facades\Media;
use Naraki\Rss\Contracts\RssFeedable;

class Home extends Blog implements RssFeedable
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        [$posts, $media] = $this->getData();
        $this->makeFeed(
            route_i18n('home'),
            route('rss', ['type' => 'home'])
        );
        $this->makeItems($posts, $media);

        return $this->feed->render();
    }

    private function getData()
    {
        $dbResult = BlogRepo::buildWithScopes(
            [
                'blog_post_title as title',
                'blog_post_excerpt as excerpt',
                'published_at as date',
                'full_name as author',
                'entity_types.entity_type_id as type',
                'blog_post_slug as slug',
            ],
            ['entityType', 'person'])
            ->orderBy('published_at', 'desc')
            ->where('language_id', Language::getAppLanguageId())
            ->limit(20)
            ->get();

        $dbImages = Media::image()->getImages(
            $dbResult->pluck('type')->all(), [
                'media_uuid as uuid',
                'media_extension as ext',
                'entity_types.entity_type_id as type',
                'entity_id'
            ]
        );

        $media = [];
        foreach ($dbImages as $image) {
            $media[$image->type] = $image;
        }

        return [$dbResult, $media];
    }


}