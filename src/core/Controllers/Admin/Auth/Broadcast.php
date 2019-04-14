<?php namespace Naraki\Core\Controllers\Admin\Auth;

use Naraki\Core\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast as BroadcastFacade;

class Broadcast extends Controller
{
    /**
     * Authenticate the request for channel access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        return BroadcastFacade::auth($request);
    }

}