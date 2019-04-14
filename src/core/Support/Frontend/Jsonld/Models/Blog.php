<?php namespace Naraki\Core\Support\Frontend\Jsonld\Models;

use Naraki\Core\Support\Frontend\Jsonld\JsonLd;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\CreativeWork\Article\NewsArticle;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Intangible\BreadcrumbList;
use Carbon\Carbon;

class Blog extends General
{
    /**
     * @param \stdClass $data
     * @param \Naraki\System\Support\Settings $settings
     * @return string
     */
    public static function makeStructuredData($data, $settings = null): string
    {
        $structuredData = [];
        $class = BreadcrumbList::class;
        $jsonld = ['itemListElement' => []];
        foreach ($data->breadcrumbs as $idx => $breadCrumb) {
            $jsonld['itemListElement'][] = [
                'position' => $idx + 1,
                'name' => $breadCrumb['label'],
                'item' => $breadCrumb['url']
            ];
        }
        $structuredData[] = (object)compact('class', 'jsonld');
        $class = NewsArticle::class;

        $jsonld = [
            'mainEntityOfPage@WebPage' => [
                'url' => route_i18n('blog', ['slug' => $data->post->getAttribute('slug')])
            ],
            'headline' => $data->post->getAttribute('title'),
            'datePublished' => (new Carbon($data->post->getAttribute('date_published')))->format('Y-m-d\TH:i:s'),
            'dateModified' => (new Carbon($data->post->getAttribute('date_modified')))->format('Y-m-d\TH:i:s'),
            'author@Person' => [
                '@id' => sprintf('%s#person', route_i18n(
                        'blog.author',
                        ['slug' => $data->post->getAttribute('author')])
                )
            ],
            'description' => $data->post->getAttribute('excerpt')
        ];
        if (!is_null($data->media)) {
            $jsonld['image'] = [
                asset($data->media->present('asset')),
                asset($data->media->present('thumbnail')),
            ];
        }
        if ($settings->get('entity_type') === 'person') {
            $jsonld['publisher'] = [
                '@id' => sprintf('%s#%s', $settings->get('person_url'), 'person')
            ];
        } else {
            $jsonld['publisher'] = [
                '@id' => sprintf('%s#%s', $settings->get('org_url'), strtolower($settings->get('org_type')))
            ];
        }
        $structuredData[] = (object)compact('class', 'jsonld');

        return JsonLd::generate($structuredData);

    }

}