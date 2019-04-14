<?php namespace Naraki\Core\Support\Frontend\Social;

use Naraki\Core\Models\Language;

class General
{

    public static function getFacebookTagList(string $title, string $description, array $input): string
    {
        $logoFilename = env('APP_LOGO_FILENAME');
        $tagList = [
            'og:title' => $title,
            'og:url' => route_i18n('home'),
            'og:image' => asset(sprintf('media/img/site/%s', $logoFilename)),
            'og:description' => $description,
            'og:site_name' => config('app.name'),
            'og:type'=>'website',
            'og:locale'=>Language::getAppLanguageISO639(),
        ];
        if (!is_null($input['facebook_app_id']) && !empty($input['facebook_app_id'])) {
            $tagList['fb:app_id'] = $input['facebook_app_id'];
        }
        $tags = '';
        foreach ($tagList as $k => $v) {
            $tags .= sprintf('<meta property="%s" content="%s">', $k, $v);
        }
        return $tags;
    }

    public static function getTwitterTagList(string $title, string $description, array $input): string
    {
        $tagList = [
            'twitter:title' => $title,
            'twitter:description' => $description,
            'twitter:image:src' => asset(sprintf('media/img/site/%s', env('APP_LOGO_FILENAME'))),
            'twitter:site' => $input['twitter_publisher'],
        ];
        $tags = '';
        foreach ($tagList as $k => $v) {
            $tags .= sprintf('<meta name="%s" content="%s">', $k, $v);
        }
        return $tags;
    }

}