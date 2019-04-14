<?php namespace Naraki\Rss\Feeds;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed as SuinFeed;
use Suin\RSSWriter\Item;

abstract class Feed implements Responsable
{
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var \Suin\RSSWriter\Feed
     */
    protected $feed;
    /**
     * @var \Suin\RSSWriter\Channel
     */
    protected $channel;

    protected function __construct()
    {
        $this->feed = new SuinFeed();
        $this->channel = new Channel();
    }

    public function toResponse($request): Response
    {
        return new Response($this, 200, [
            'Content-Type' => 'application/xml;charset=UTF-8',
        ]);
    }

    /**
     * @param \stdClass $d
     * @return void
     */
    protected function buildFeed($d)
    {
        $this->channel->title($d->title)
            ->description($d->description)
            ->url($d->url)
            ->feedUrl($d->feedUrl)
            ->language($d->locale)
            ->copyright(sprintf('Copyright %s, %s', $d->copyrightDate, $d->copyrightName))
            ->pubDate($d->nowDate)
            ->lastBuildDate($d->nowDate)
            ->appendTo($this->feed);
    }

    /**
     * @param \stdClass $d
     * @return void
     */
    protected function buildItem($d)
    {
        $item = new Item();
        $item
            ->title($d->title)
            ->description($d->description)
            ->contentEncoded($d->content)
            ->author($d->author)
            ->url($d->url)
            ->guid($d->url, true)
            ->pubDate($d->date)
            ->preferCdata(true)
            ->appendTo($this->channel);
    }

    /**
     * @param string $content
     * @param $image
     * @return string
     */
    protected function buildContent($content, $image): string
    {
        return sprintf(
            '%s<p>%s</p>',
            (!is_null($image)) ? sprintf('<p style="text-align:center;"><img src="%s"/></p>', $image) : '',
            $content
        );
    }

}