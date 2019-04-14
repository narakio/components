<?php namespace Naraki\Sitemap\Sitemaps;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Interfaces\VisitorInterface;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use Thepixeldeveloper\Sitemap\Sitemap as XmlSitemap;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;

abstract class Sitemap implements Responsable
{
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var \Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver
     */
    protected $driver;
    /**
     * @var int
     */
    protected $priority;
    /**
     * @var int
     */
    protected $changeFrequency;

    protected function __construct()
    {
        $this->driver = new XmlWriterDriver();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request): Response
    {
        return new Response($this, 200, [
            'Content-Type' => 'application/xml;charset=UTF-8',
        ]);
    }

    /**
     * @param \Thepixeldeveloper\Sitemap\SitemapIndex $smIndex
     * @param string $route
     * @param \DateTimeInterface $lastMod
     * @return void
     */
    protected function addSitemap(SitemapIndex $smIndex, string $route, \DateTimeInterface $lastMod)
    {
        $sm = new XmlSitemap($route);
        $sm->setLastMod($lastMod);
        $smIndex->add($sm);
    }

    /**
     * @param \Thepixeldeveloper\Sitemap\Urlset $urlSet
     * @param \stdClass $params
     * @return void
     */
    protected function addUrl(Urlset $urlSet, \stdClass $params)
    {
        $url = new Url($params->url);
        $url->setLastMod($params->date);
        $url->setChangeFreq($params->freq);
        $url->setPriority($params->priority);
        $urlSet->add($url);
    }

    /**
     * @param \Thepixeldeveloper\Sitemap\Interfaces\VisitorInterface $index
     * @return string
     */
    protected function output(VisitorInterface $index)
    {
        $this->withStylesheet();
        $index->accept($this->driver);
        return $this->driver->output();
    }

    /**
     * @param string $type
     * @param string $slug
     * @return string
     */
    protected function makeRoute($type, $slug = null): string
    {
        $params = ['type' => $type];
        if (!is_null($slug)) {
            $params['slug'] = $slug;
        }
        return route('sitemap', $params);
    }

    /**
     * @return \Naraki\Sitemap\Sitemaps\Sitemap
     */
    protected function withStylesheet(): self
    {
        if (env('APP_ENV') === 'local') {
            $this->driver->addProcessingInstructions(
                'xml-stylesheet',
                'type="text/xsl" href="/resources/sitemap.xsl"'
            );
        }
        return $this;
    }
}