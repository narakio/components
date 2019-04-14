<?php namespace Naraki\Core\Composers\Frontend;

use Naraki\Core\Composer;
use Naraki\Core\Facades\JavaScript;

class Profile extends Composer
{
    public function compose($view)
    {
        JavaScript::putArray([
            'token' => \Session::get('jwt_token'),
            'user' => auth()->user()->only(['username'])
        ]);
    }

}