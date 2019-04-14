<?php namespace Naraki\Core\Support\Frontend;

class Breadcrumbs
{
    private $breadcrumbs = '';

    public static function render($chain)
    {
        return (new self())->make($chain);
    }

    private function make($chain)
    {
        $this->breadcrumbs = '<ul class="breadcrumbs">';
        foreach ($chain as $item) {
            $this->addNode((object)$item);
        }
        $this->breadcrumbs .= '</ul>';
        return $this->breadcrumbs;
    }

    private function addNode(\stdClass $node)
    {
        $this->breadcrumbs .= '<li><span class="breadcrumb-item">';
        if (isset($node->url)) {
            $this->breadcrumbs .= sprintf('
      <a class="breadcrumb-link" href="%s">%s</a>', $node->url, $node->label);
        } else {
            $this->breadcrumbs .= sprintf('<span>%s</span>', $node->label);
        }
        $this->breadcrumbs .= '</span></li>';
    }

}