<?php namespace Naraki\Core\Support\Frontend\Rss;

use Naraki\Core\Models\Language;
use Carbon\Carbon;
use Naraki\Rss\Feeds\Feed as AbstractFeed;
use Naraki\System\Support\Settings;

abstract class Feed extends AbstractFeed
{
    protected function makeFeed($url, $feedUrl)
    {
        $nowDate = Carbon::now();
        $settings = Settings::getSettings('general_formatted');
        $this->buildFeed((object)[
            'title' => $settings->get('meta_title'),
            'description' => $settings->get('meta_description'),
            'url' => $url,
            'feedUrl' => $feedUrl,
            'locale' => Language::getAppLanguageISO639(),
            'copyrightDate' => $nowDate->year,
            'copyrightName' => config('app.name'),
            'nowDate' => strtotime($nowDate->toDateTimeString()),
        ]);
    }

}