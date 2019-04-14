<?php

namespace Naraki\Core\Controllers\Frontend\Auth;

use Naraki\Core\Controllers\Frontend\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPassword extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('core::frontend.auth.passwords.email');
    }


}
