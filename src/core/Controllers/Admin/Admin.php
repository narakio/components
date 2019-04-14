<?php namespace Naraki\Core\Controllers\Admin;

class Admin extends Controller
{

    public function index()
    {
        return view('core::admin.default');
    }

}