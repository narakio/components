<?php namespace Naraki\Sentry\Controllers\Frontend;

use Illuminate\Http\Request;
use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Sentry\Facades\User as UserProvider;

class User extends Controller
{
    public function show($slug)
    {

    }

    public function delete(Request $request)
    {
        $user = auth()->user()->getAttribute('username');
        \Auth::guard('web')->logout();
        \Auth::guard('jwt')->logout();
        $request->session()->invalidate();
        UserProvider::deleteByUsername($user);
    }

}