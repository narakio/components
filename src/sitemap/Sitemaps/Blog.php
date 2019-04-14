<?php namespace Naraki\Sitemap\Sitemaps;

use Carbon\Carbon;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Sitemap\Contracts\Sitemapable;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use Thepixeldeveloper\Sitemap\Urlset;

class Blog extends Sitemap implements Sitemapable
{
    private $perPage = 1000;
    protected $priority = 0.7;
    protected $changeFrequency = 'yearly';

    public function __construct($slug)
    {
        $this->slug = $slug;
        parent::__construct();
    }

    public function __toString(): string
    {
        if (is_null($this->slug)) {
            $sm = new SitemapIndex();
            $blogCount = BlogRepo::countAll();
            $pages = intval(round($blogCount / $this->perPage));
            for ($i = 0; $i < $pages; $i++) {
                $this->addSitemap(
                    $sm,
                    $this->makeRoute('blog', $i + 1),
                    Carbon::make(BlogRepo::getNth($i * $this->perPage, 'updated_at'))
                );
            }
        } else {
            $page = intval($this->slug);
            $posts = BlogRepo::getN(
                $this->perPage,
                $page * $this->perPage,
                [
                    'blog_post_slug as slug',
                    'updated_at as date'
                ],
                'updated_at'
            )->get();
            $sm = new Urlset();
            foreach ($posts as $post) {
                $url = (object)[
                    'url' => route_i18n('blog', ['slug' => $post->getAttribute('slug')]),
                    'date' => Carbon::make($post->getAttribute('date')),
                    'freq' => $this->changeFrequency,
                    'priority' => $this->priority,
                ];
                $this->addUrl($sm, $url);
            }
        }

        return $this->output($sm);
    }

}