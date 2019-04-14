<?php namespace Naraki\Blog\Support\Social;

use Naraki\Core\Models\Language;
use Carbon\Carbon;

class Blog
{
    /**
     * @param \stdClass $data
     * @param \Naraki\System\Support\Settings $settings
     * @return string
     */
    public function getFacebookTagList(\stdClass $data, $settings): string
    {
        $tagList = [
            'og:title' => $data->post->getAttribute('title'),
            'og:url' => route_i18n('blog', ['slug' => $data->post->getAttribute('slug')]),
            'og:description' => $data->post->getAttribute('excerpt'),
            'og:site_name' => config('app.name'),
            'og:author' => $data->post->getAttribute('person'),
            'og:type' => 'article',
            'og:locale' => Language::getLanguageName(intval($data->post->getAttribute('language'))),
            'og:article:published_time' => (new Carbon($data->post->getAttribute('date_published')))
                ->format('Y-m-d\TH:i:s'),
        ];
        if (!is_null($settings->get('facebook_app_id')) && !empty($settings->get('facebook_app_id'))) {
            $tagList['fb:app_id'] = $settings->get('facebook_app_id');
        }
        if (!is_null($data->media)) {
            $tagList['og:image'] = asset($data->media->present('asset'));
        }

        $tags = '';
        foreach ($tagList as $k => $v) {
            $tags .= sprintf('<meta property="%s" content="%s">', $k, $v);
        }
        return $tags;
    }

    /**
     * @param \stdClass $data
     * @param \Naraki\System\Support\Settings $settings
     * @return string
     */
    public function getTwitterTagList(\stdClass $data, $settings): string
    {
        $tagList = [
            'twitter:title' => $data->post->getAttribute('title'),
            'twitter:description' => $data->post->getAttribute('excerpt'),
            'twitter:site' => $settings->get('twitter_publisher'),
            'twitter:card' => 'summary_large_image'
        ];
        if (!is_null($data->media)) {
            $tagList['twitter:image:src'] = asset($data->media->present('asset'));
        }
        $tags = '';
        foreach ($tagList as $k => $v) {
            $tags .= sprintf('<meta name="%s" content="%s">', $k, $v);
        }
        return $tags;
    }
}