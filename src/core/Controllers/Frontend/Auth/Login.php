<?php namespace Naraki\Core\Controllers\Frontend\Auth;

use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Sentry\Facades\User as UserProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class Login extends Controller
{
    use AuthenticatesUsers;

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $status = Session::get('status');
        return view('core::frontend.auth.login', compact('status'));
    }

    /**
     * In cases where we click on the login button from a particular page
     * so we can get back to it when we're logged in.
     *
     * @param string $type
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function loginRedirect($type, $slug)
    {
        Session::put('url.intended', route_i18n($type, ['slug' => $slug]));
        return redirect(route_i18n('login'));

    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $token = \Auth::guard('jwt')->attempt($this->credentials($request));

        if ($token) {
            Session::put('jwt_token', $token);
            return true;
        }
        return false;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->guard()->login(\Auth::guard('jwt')->user());

        $this->clearLoginAttempts($request);

        return redirect()->intended(route_i18n('home'));
    }

    /**
     * Get the failed login response instance.
     *
     * @return void
     */
    protected function sendFailedLoginResponse()
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        \Auth::guard('jwt')->logout();
        $request->session()->invalidate();
        return redirect()->back();
    }

    /**
     * @param string $token
     * @return \Illuminate\Routing\Redirector
     */
    public function activate($token)
    {
        if (is_hex_uuid_string($token)) {
            $nbDeletedRecords = UserProvider::activate($token);
            if ($nbDeletedRecords === 1) {
                return redirect(route_i18n('login'))->with('status', 'activated');
            }
        }
        return redirect(route_i18n('login'))->with('status', 'activation_error');
    }

}
