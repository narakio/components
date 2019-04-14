<?php namespace Naraki\Oauth\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Oauth\Exceptions\EmailNotVerified;
use Naraki\Oauth\Socialite\GoogleUser;
use Naraki\Sentry\Facades\User as UserProvider;

class OAuth extends Controller
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
     * Redirect the user to the provider authentication page.
     *
     * @param  string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if ($provider == 'twitter') {
            return Socialite::driver($provider)->redirect()->getTargetUrl();
        }
        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param  string $provider
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider, Request $request)
    {
        if ($provider !== 'twitter') {
            $socialiteUser = Socialite::driver($provider)->stateless()->user();
        } else {
            $socialiteUser = Socialite::driver($provider)->user();
        }
        $this->processUser($request, $provider, $socialiteUser);
        \Cookie::queue('oauth_verified', true, 60 * 24 * 365);

        return view('oauth::callback', ['route' => route_i18n('home')]);
    }

    /**
     * Called when the google smartlock popup shows and the user dismisses it.
     * We save a cookie to ensure that the popup doesn't show again for two weeks.
     *
     * @return void
     */
    public function googleYoloDismiss()
    {
        \Cookie::queue('oauth_verified', true, 60 * 24 * 15);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function googleYolo(Request $request)
    {
        $provider = 'google';
        $input = $request->only(['google_token', 'avatar']);
        $jwt = new JWT();
        //Default leeway defaults to 1 second which is insufficient to do the token check in time.
        $jwt::$leeway = 10;
        $client = new \Google_Client([
            'client_id' => config('services.google.client_id'),
            'jwt' => $jwt
        ]);
        $tokenContents = $client->verifyIdToken($input['google_token']);
        if ($tokenContents !== false) {
            $tokenContents['picture'] = $input['avatar'];
            try {
                $this->processUserYolo($request, $provider, new GoogleUser($tokenContents));
            } catch (EmailNotVerified $e) {
                return response(
                    trans('error.http.422.oauth_email_unverif'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            \Cookie::queue('oauth_verified', true, 60 * 60 * 24 * 365);
            return response(null, Response::HTTP_NO_CONTENT);
        } else {
            return response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     * @param \Naraki\Oauth\Socialite\GoogleUser $socialiteUser
     */
    private function processUserYolo($request, $provider, GoogleUser $socialiteUser)
    {
        $user = UserProvider::processViaYolo($provider, $socialiteUser);
        $this->login($request, $user);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     * @param \Laravel\Socialite\Contracts\User $socialiteUser
     */
    private function processUser($request, $provider, SocialiteUser $socialiteUser)
    {
        $user = UserProvider::processViaOAuth($provider, $socialiteUser);
        $this->login($request, $user);

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $user
     */
    private function login($request, $user)
    {
        if (env('APP_ENV') !== 'testing') {
            $request->session()->regenerate();
        }

        \Auth::guard('jwt')->login($user);
        \Session::put('jwt_token', \Auth::guard('jwt')->login($user));
        $this->guard()->login($user, true);
        $this->clearLoginAttempts($request);
    }

}
