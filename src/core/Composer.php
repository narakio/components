<?php namespace Naraki\Core;

abstract class Composer {


    /**
     * @param array $data
     * @param \Illuminate\View\View $view
     * @return void
     */
    protected function addVarsToView(array $data, $view){
        foreach ($data as $k => $v) {
            $view->with($k, $v);
        }
    }
}
