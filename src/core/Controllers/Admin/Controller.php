<?php namespace Naraki\Core\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var \Naraki\Sentry\Models\User
     */
    protected $user;
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct()
    {
        auth()->setDefaultDriver('jwt');
        $this->user = auth()->user();
    }

}