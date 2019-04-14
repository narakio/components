<?php namespace Naraki\Blog\Support\Rss;

use Naraki\Core\Models\Language;
use Naraki\Rss\Contracts\RssFeedable;
use Naraki\Media\Facades\Media;
use Naraki\Blog\Facades\Blog as BlogRepo;

class Tag extends Blog implements RssFeedable
{
    protected static $type = 'tag';

    public function __construct($slug)
    {
        $this->slug = $slug;
        parent::__construct();
    }

    public function getData()
    {
        $dbResult = BlogRepo::buildWithScopes([
            'blog_post_title as title',
            'blog_post_excerpt as excerpt',
            'blog_post_slug as slug',
            'published_at as date',
            'entity_types.entity_type_id as type',
            'person_slug as author',
            'blog_tag_name as tag'
        ], ['entityType', 'person', 'tag' => slugify($this->slug)])
            ->orderBy('published_at', 'desc')
            ->where('language_id', Language::getAppLanguageId())
            ->limit(15)
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