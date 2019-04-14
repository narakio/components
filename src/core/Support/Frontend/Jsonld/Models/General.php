<?php namespace Naraki\Core\Support\Frontend\Jsonld\Models;

use Naraki\Core\Support\Frontend\Jsonld\JsonLd;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\CreativeWork\CreativeWork;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Organization\Organization;
use Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Person;

class General
{
    public static function makeStructuredData($data, $settings = null): string
    {
        $structuredData = [];
        switch ($data['entity_type']) {
            case 'person':
                $class = Person::class;
                $jsonld = [
                    'name' => $data['person_name'],
                    'url' => $data['person_url'],
                ];
                $structuredData[] = (object)compact('class', 'jsonld');
                $class = CreativeWork::getClassName($data['website_type']);
                $jsonld = [
                    'url' => route_i18n('home'),
                    'potentialAction@SearchAction' => [
                        "target" => str_replace(
                            'queryString',
                            '{q}',
                            route_i18n('search', 'queryString')
                        ),
                        "query-input" => "required name=q"
                    ],
                    'publisher@Person' => [
                        '@id' => sprintf('%s#%s', $data['person_url'], 'person')
                    ],
                ];
                if (!empty($data['links'])) {
                    $jsonld['sameAs'] = $data['links'];
                }
                $structuredData[] = (object)compact('class', 'jsonld');
                break;
            case 'organization':
                $class = Organization::getClassName($data['org_type']);
                $jsonld = [
                    'url' => $data['person_url'],
                ];
                if (isset($data['logo'])) {
                    $jsonld['logo'] = $data['logo'];
                }
                $structuredData[] = (object)compact('class', 'jsonld');
                $class = CreativeWork::getClassName($data['website_type']);
                $jsonld = [
                    'url' => route_i18n('home'),
                    'potentialAction@SearchAction' => [
                        "target" => str_replace(
                            'queryString',
                            '{q}',
                            route_i18n('search', 'queryString')
                        ),
                        "query-input" => "required name=q"
                    ],
                    sprintf('publisher@%s', $data['org_type']) => [
                        '@id' => sprintf('%s#%s', $data['org_url'], strtolower($data['org_type']))
                    ],
                ];
                if (!empty($data['links'])) {
                    $jsonld['sameAs'] = $data['links'];
                }
                $structuredData[] = (object)compact('class', 'jsonld');
                break;
        }
        return JsonLd::generate($structuredData);
    }

    public static function organizationList(): array
    {
        $orgs = [];
        foreach (Organization::$organizationList as $org => $v) {
            $orgs[$org] = trans(sprintf('internal.jsonld.organizations.%s', $org));
        }
        return $orgs;
    }

    public static function websiteList(): array
    {
        $ws = [];
        foreach (CreativeWork::$websiteList as $w => $v) {
            $ws[$w] = trans(sprintf('internal.jsonld.websites.%s', $w));
        }
        return $ws;
    }


}