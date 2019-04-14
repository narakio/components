<?php namespace Naraki\Blog\Support\Rss;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Naraki\Rss\Contracts\RssFeedable;

class Search extends Blog implements RssFeedable
{
    /**
     * @var \stdClass
     */
    private $data;

    public function __construct($slug)
    {
        $this->slug = $slug;
        parent::__construct();
    }

    public function __toString(): string
    {
        $this->getData();
        $this->makeFeed(
            route_i18n('search', ['q' => $this->slug]),
            route('rss', ['type' => 'search', 'slug' => $this->slug])
        );

        foreach ($this->data as $p) {
            $this->buildItem((object)[
                'title' => $p->title,
                'description' => $p->meta->excerpt,
                'content' => $this->buildContent(
                    $p->meta->excerpt,
                    (!is_null($p->meta->image))
                        ? asset(str_replace('_tb.', '_ft.', $p->meta->image->url))
                        : null
                ),
                'author' => $p->meta->author->name,
                'url' => $p->meta->url,
                'date' => (!is_null($p->date)) ? strtotime($p->date) : strtotime(Carbon::now())
            ]);
        }
        return $this->feed->render();
    }

    private function getData()
    {
        $slug = $this->slug;
        $client = new Client();
        $request = new \GuzzleHttp\Psr7\Request(
            'POST',
            sprintf('%s/search/blog/?q=%s&size=15&sort=title&order=asc', env('ELASTIC_SEARCH_URL'), $slug)
        );

        $instance = $this;

        $promise = $client->sendAsync($request)->then(function ($response) use ($instance) {
            /**
             * @var $response \GuzzleHttp\Psr7\Response
             */
            $instance->fetchAsyncData(json_decode($response->getBody()->getContents()));
        });
        $promise->wait();
    }

    private function fetchAsyncData($data)
    {
        $this->data = $data->articles;
    }


}