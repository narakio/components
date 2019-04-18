<?php namespace Naraki\Blog\Support\Collections;

use \Illuminate\Support\Collection;
use Naraki\Blog\Support\Presenters\BlogPost as BlogPostPresenter;
use Naraki\Core\Traits\Models\Presentable;

class Blog extends Collection
{
    use Presentable;

    protected $presenter = BlogPostPresenter::class;

    public function getAttribute($key)
    {
        return $this->get($key);
    }

}