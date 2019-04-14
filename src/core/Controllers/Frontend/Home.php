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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $dbResult = Blog::buildForDisplay()
            ->orderBy('page_views', 'desc')
            ->where('language_id', Language::getAppLanguageId())
            ->where('blog_categories.parent_id', null)
            ->limit(115)
            ->get();
        $dbImages = MediaProvider::image()->getImages(
            $dbResult->pluck('type')->all(), [
                'media_uuid as uuid',
                'media_extension as ext',
                'entity_types.entity_type_id as type',
                'entity_id'
            ]
        );
        $media = [];
        foreach ($dbImages as $image) {
            $media[$image->type] = $image;
        }
        $posts = [
            'featured' => [],
            'most_viewed_cat' => [],
            'most_viewed' => []
        ];

        foreach ($dbResult as $post) {
            if ($post->featured == 1) {
                if (isset($media[$post->type])) {
                    $posts['featured'][] = $post;
                }
            } else {
                if (isset($media[$post->type])) {
                    if (isset($posts['most_viewed_cat'][$post->cat])) {
                        if (count($posts['most_viewed_cat'][$post->cat]) < 5) {
                            $posts['most_viewed_cat'][$post->cat][] = $post;
                        } else {
                            if (count($posts['most_viewed']) < 18) {
                                $posts['most_viewed'][] = $post;
                            }
                        }
                    } else {
                        $posts['most_viewed_cat'][$post->cat][] = $post;
                    }
                }
            }
        }
        $posts = [
            'featured' => [],
            'most_viewed_cat' => [],
            'most_viewed' => []
        ];
        unset($dbResult);
        return view('core::frontend.site.home', compact('posts', 'media'));
    }

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