<?php namespace Naraki\Core\Middleware\Frontend;

use Closure;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::guard('web')->check()) {
            return $next($request);
        }
        if ($request->wantsJson()) {
            return response(null, 401);
        }
        return redirect()->guest(route_i18n('home'));
    }
}
