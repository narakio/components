<?php namespace Naraki\Blog\Support\Rss;

use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Media\Facades\Media;
use Naraki\Rss\Contracts\RssFeedable;

class Author extends Blog implements RssFeedable
{
    protected static $type = 'author';

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
            'blog_category_slug as cat',
            'entity_types.entity_type_id as type',
            'full_name as author'
        ], ['entityType', 'person' => slugify($this->slug), 'language', 'category'])
            ->where('parent_id', null)
            ->orderBy('published_at', 'desc')
            ->limit(8)
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