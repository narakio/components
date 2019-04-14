<?php namespace Naraki\Sitemap\Sitemaps;

use Carbon\Carbon;
use Naraki\Sitemap\Contracts\Sitemapable;
use Naraki\System\Support\Settings;
use Thepixeldeveloper\Sitemap\Urlset;

class Pages extends Sitemap implements Sitemapable
{
    protected $priority = 1;
    protected $changeFrequency = 'yearly';

    public function __construct($slug)
    {
        $this->slug = $slug;
        parent::__construct();
    }

    public function __toString(): string
    {

        $sm = new Urlset();
        $this->addUrl($sm, (object)[
            'url' => route_i18n('home'),
            'date' => static::homePageLastModified(),
            'freq' => $this->changeFrequency,
            'priority' => $this->priority,
        ]);

        $sitemaps = Settings::sitemap(false);
        if (!is_null($sitemaps)) {
            if ($sitemaps['sitemap'] === true) {
                foreach ($sitemaps['links'] as $sitemap) {
                    $url = (object)[
                        'url' => $sitemap['link'],
                        'date' => Carbon::make($sitemap['date']),
                        'freq' => $this->changeFrequency,
                        'priority' => $this->priority,
                    ];
                    $this->addUrl($sm, $url);
                }
            }
        }
        return $this->output($sm);
    }

    /**
     * @return \Carbon\Carbon
     */
    public static function homePageLastModified()
    {
        $path = base_path('.env');
        if (is_file($path)) {
            $stat = @stat($path);
            if ($stat !== false) {
                return Carbon::createFromTimestamp($stat['mtime']);
            }
        }
        return Carbon::now();
    }

}