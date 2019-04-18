<?php namespace Naraki\Blog\Composers;

use Naraki\Blog\Facades\Blog;
use Naraki\Core\Composer;

class Home extends Composer
{
    /**
     * @param \Illuminate\View\View $view
     */
    public function compose($view)
    {
        $data = $view->getData();
        $data['blog_mvp'] = Blog::mostViewedByCategory();
        $data['blog_categories'] = array_keys($data['blog_mvp']);
        $this->addVarsToView($data, $view);
    }

}