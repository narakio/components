<?php namespace Naraki\Core\Controllers\Frontend\Auth;

use Naraki\Sentry\Events\UserRegistered;
use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Sentry\Requests\Frontend\CreateUser;
use Naraki\Sentry\Facades\User as UserProvider;

class Register extends Controller
{
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('core::frontend.auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Naraki\Sentry\Requests\Frontend\CreateUser $request
     * @return \Illuminate\Http\Response
     */
    public function register(CreateUser $request)
    {
        $user = UserProvider::createOne($request->except(['timezone']));
        UserProvider::updateStats($user,$request->only(['stat_user_timezone']));

        event(new UserRegistered($user, UserProvider::generateActivationToken($user)));
        return redirect(route_i18n('login'))->with('status', 'registered');
    }

}
