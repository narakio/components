<?php namespace Naraki\Core\Controllers\Admin;

use Illuminate\Foundation\Auth\ThrottlesLogins;

class Auth
{
    use ThrottlesLogins;
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

}
