<?php namespace Naraki\Blog\Support\Rss;

use Naraki\Core\Models\Language;
use Naraki\Rss\Contracts\RssFeedable;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Media\Facades\Media;

class Category extends Blog implements RssFeedable
{
    protected static $type = 'category';

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
            'published_at as date',
            'full_name as author',
            'entity_types.entity_type_id as type',
            'blog_post_slug as slug',
        ], ['entityType', 'person', 'category' => $this->slug])
            ->orderBy('published_at', 'desc')
            ->where('blog_categories.parent_id', null)
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