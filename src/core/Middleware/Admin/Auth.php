<?php namespace Naraki\Core\Middleware\Admin;

use Closure;
use Illuminate\Http\Response;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!\Auth::guard('jwt')->check()) {
            return response(trans('error.http.401'),Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
