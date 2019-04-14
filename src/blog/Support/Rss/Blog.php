<?php namespace Naraki\Blog\Support\Rss;

use Naraki\Core\Models\Language;
use Carbon\Carbon;
use Naraki\Rss\Feeds\Feed as AbstractFeed;

abstract class Blog extends AbstractFeed
{
    protected static $type = null;

    protected function makeFeed($url, $feedUrl)
    {
        $nowDate = Carbon::now();
        $this->buildFeed((object)[
            'title' => \Cache::get('meta_title'),
            'description' => \Cache::get('meta_description'),
            'url' => $url,
            'feedUrl' => $feedUrl,
            'locale' => Language::getAppLanguageISO639(),
            'copyrightDate' => $nowDate->year,
            'copyrightName' => config('app.name'),
            'nowDate' => strtotime($nowDate->toDateTimeString()),
        ]);
    }

    /**
     * @param \Illuminate\Support\Collection $posts
     * @param \Illuminate\Support\Collection $media
     */
    protected function makeItems($posts, $media)
    {
        /**
         * @var \Naraki\Blog\Models\BlogPost $p
         * @var \Naraki\Media\Models\MediaEntity[] $media
         */
        foreach ($posts as $p) {
            $this->buildItem((object)[
                'title' => $p->getAttribute('title'),
                'description' => $p->getAttribute('excerpt'),
                'content' => $this->buildContent(
                    $p->getAttribute('excerpt'),
                    (isset($media[$p->getAttribute('type')])) ?
                        $media[$p->getAttribute('type')]->present('asset') :
                        null
                ),
                'author' => $p->getAttribute('author'),
                'url' => route_i18n('blog', ['slug' => $p->getAttribute('slug')]),
                'date' => strtotime($p->getAttribute('date')),
            ]);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        [$posts, $media] = $this->getData();
        $this->makeFeed(
            route_i18n(sprintf('blog.%s', static::$type), ['slug' => $this->slug]),
            route('rss', ['type' => static::$type, 'slug' => $this->slug])
        );
        $this->makeItems($posts, $media);

        return $this->feed->render();
    }

}