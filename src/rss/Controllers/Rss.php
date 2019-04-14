<?php namespace Naraki\Rss\Controllers;

use Illuminate\Routing\Controller;
use Naraki\Rss\Contracts\RssFeedable;

class Rss extends Controller
{
    /**
     * @param string $type
     * @param string $slug
     * @return \Illuminate\Contracts\Support\Responsable|\Naraki\Rss\Contracts\RssFeedable
     */
    public function __invoke($type, $slug = null): RssFeedable
    {
        $feedableClasses = config('rss.aliases');
        if (!isset($feedableClasses[$type])) {
            throw new \UnexpectedValueException(
                sprintf('%s does not have an RSS feed maker', $type)
            );
        }
        $instance = new $feedableClasses[$type]($slug);
        if(!$instance instanceof RssFeedable){
            throw new \UnexpectedValueException(
                sprintf('%s must implement interface \Naraki\Rss\Contracts\RssFeedable', get_class($instance))
            );
        }
        return $instance;
    }
}