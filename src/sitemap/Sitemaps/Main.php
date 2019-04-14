<?php namespace Naraki\Sitemap\Sitemaps;

use Carbon\Carbon;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Sitemap\Contracts\Sitemapable;
use Thepixeldeveloper\Sitemap\SitemapIndex;

class Main extends Sitemap implements Sitemapable
{

    public function __construct()
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        $smIndex = new SitemapIndex();
        $date = BlogRepo::getNth(0, 'updated_at');
        $lastModified = Carbon::make($date ?? Carbon::now());

        $this->addSitemap($smIndex, route('sitemap', ['type' => 'pages']),
            Pages::homePageLastModified()
        );
        $this->addSitemap($smIndex, route('sitemap', ['type' => 'blog']),
            $lastModified
        );
        $this->addSitemap($smIndex, route('sitemap', ['type' => 'authors']),
            $lastModified
        );
        $this->addSitemap($smIndex, route('sitemap', ['type' => 'tags']),
            $lastModified
        );

        return $this->output($smIndex);
    }

}