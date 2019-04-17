<?php namespace Naraki\Core\Controllers\Frontend;

use Naraki\Core\Models\Language;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Naraki\Blog\Facades\Blog;
use Naraki\Media\Facades\Media as MediaProvider;

class Home extends Controller
{

    /**
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale($locale)
    {
        Session::put('locale', $locale);
        $previousRoute = Route::getRoutes()->match(app('request')->create(URL::previous()));
        $locales = config('app.locales');
        if (array_key_exists($locale, $locales)) {
            //locale routes are prefixed with the locale code, ie. fr.<route_name>
            //we want to strip off that part and add another prefix if needed
            if(strpos($previousRoute->getName(),app()->getLocale().'.')===0){
                $routeName = substr($previousRoute->getName(),3);
            }else{
                $routeName = $previousRoute->getName();
            }
            app()->setLocale($locale);
            $nextRoute = route_i18n($routeName, $previousRoute->parameters());
            return redirect()->to($nextRoute);
        }
        return redirect()->back();

    }

}