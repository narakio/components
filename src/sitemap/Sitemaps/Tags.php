<?php namespace Naraki\Sitemap\Sitemaps;

use Carbon\Carbon;
use Naraki\Blog\Facades\Blog as BlogRepo;
use Naraki\Sitemap\Contracts\Sitemapable;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use Thepixeldeveloper\Sitemap\Urlset;

class Tags extends Sitemap implements Sitemapable
{
    private $perPage = 1000;
    protected $priority = 0.3;
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
            $authorCount = BlogRepo::tag()->countAll();
            $pages = intval(round($authorCount / $this->perPage));
            for ($i = 0; $i < $pages; $i++) {
                $this->addSitemap(
                    $sm,
                    $this->makeRoute(
                        substr(strtolower(__CLASS__),strrpos(__CLASS__,'\\')+1),
                        $i + 1),
                    Carbon::make(
                        BlogRepo::tag()->getNth($i * $this->perPage, 'blog_posts.updated_at')
                    )
                );
            }
        } else {
            $page = intval($this->slug);
            $author = BlogRepo::tag()->getN(
                $this->perPage,
                $page * $this->perPage,
                [
                    'blog_tag_id',
                    'blog_tag_slug',
                    'blog_posts.updated_at'
                ],
                'blog_posts.updated_at'
            )->get();
            $sm = new Urlset();
            foreach ($author as $post) {
                $url = (object)[
                    'url' => route_i18n('blog.tag', ['slug' => $post->getAttribute('blog_tag_slug')]),
                    'date' => Carbon::make($post->getAttribute('updated_at')),
                    'freq' => $this->changeFrequency,
                    'priority' => $this->priority,
                ];
                $this->addUrl($sm, $url);
            }
        }
        return $this->output($sm);


    }

}