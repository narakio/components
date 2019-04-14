<?php namespace Naraki\Core\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($locale = $this->parseLocale($request)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function parseLocale($request)
    {
        $locale = Session::get('locale');
        if (is_null($locale)) {
            $locale = Cookie::get('locale');
            if (is_null($locale)) {
                $locale = $request->server('HTTP_ACCEPT_LANGUAGE');
                $locale = substr($locale, 0, strpos($locale, ',') ?: strlen($locale));
                $locales = config('app.locales');
                if (array_key_exists($locale, $locales)) {
                    Carbon::setLocale($locale);
                    Carbon::setToStringFormat(trans('internal.date.pretty'));
                    return $locale;
                }

                $locale = substr($locale, 0, 2);
                if (array_key_exists($locale, $locales)) {
                    Carbon::setLocale($locale);
                    Carbon::setToStringFormat(trans('internal.date.pretty'));
                    return $locale;
                }
                $locale = config('app.locale');
            }
        }
        Carbon::setLocale($locale);
        return $locale;
    }
}
