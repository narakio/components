<?php namespace Naraki\Core\Support\Frontend;

class Breadcrumbs
{
    private $breadcrumbs = '';

    /**
     * @param array $chain
     * @return string
     */
    public static function render(array $chain): string
    {
        return (new self())->make($chain);
    }

    /**
     * @param array $chain
     * @return string
     */
    private function make(array $chain): string
    {
        if (empty($chain)) {
            return '';
        }
        $this->breadcrumbs = '<ul class="breadcrumbs">';
        foreach ($chain as $item) {
            $this->addNode((object)$item);
        }
        $this->breadcrumbs .= '</ul>';
        return $this->breadcrumbs;
    }

    /**
     * @param \stdClass $node
     * @return void
     */
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