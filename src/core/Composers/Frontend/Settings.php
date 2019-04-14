<?php namespace Naraki\Core\Composers\Frontend;

use Naraki\Core\Composer;

class Settings extends Composer
{
    public function compose($view)
    {
        $viewEx = explode('.', $view->getName());
        $this->addVarsToView(['viewName' => array_pop($viewEx)], $view);
    }

}