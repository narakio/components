<?php namespace Naraki\Core\Routes;

use Illuminate\Routing\Router;

class Frontend
{
    public function bind(Router $router)
    {
        $availableLocales = config('app.locales');
        unset($availableLocales[app()->getLocale()]);
        $availableLocales[''] = '';
        foreach ($availableLocales as $k => $v) {
            $router->group([
                'prefix' => sprintf('/%s', $k),
                'middleware' => ['web'],
                'namespace' => 'Naraki\Core\Controllers\Frontend',
            ], call_user_func('static::defaultRouteGroup', $k));
        }

        if (config('app.env') === 'local') {
            $router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
        }

//        $router->group([
//            'prefix' => '/',
//            'middleware' => ['misc'],
//            'namespace' => 'Naraki\Core\Controllers\Frontend',
//        ], call_user_func('static::misc'));
    }

    public static function defaultRouteGroup($locale)
    {
        return function (Router $r) use ($locale) {
            $r->group([
            ], call_user_func('static::guest', $locale));

            $r->group([
                'middleware' => ['frontend_auth'],
            ], call_user_func('static::auth', $locale));
        };
    }

    public static function guest($locale)
    {
        return function (Router $r) use ($locale) {
            $r->get('lc/{locale}', 'Home@setLocale')
                ->name('home.locale');

            $r->get(trans('core::tr.routes.login', [], $locale), 'Auth\Login@index')
                ->name(i18nRouteNames($locale, 'login'))->middleware('frontend_guest');
            $r->get('login/redirect/{type}/{slug}', 'Auth\Login@loginRedirect')
                ->name('login_redirect');
            $r->get(trans('core::tr.routes.activate', [], $locale), 'Auth\Login@activate')
                ->name(i18nRouteNames($locale, 'activate'));

            $r->post('login', 'Auth\Login@login')->name('login.post');

            $r->get(trans('core::tr.routes.register', [], $locale), 'Auth\Register@showRegistrationForm')
                ->name(i18nRouteNames($locale, 'register'));
            $r->post('register', 'Auth\Register@register')->name('register.do');

            $r->get(
                trans('core::tr.routes.password_reset', [], $locale),
                'Auth\ForgotPassword@showLinkRequestForm'
            )->name(i18nRouteNames($locale, 'password.request'));
            $r->post('password/email', 'Auth\ForgotPassword@sendResetLinkEmail')
                ->name('password.email');
            $r->get(
                trans('core::tr.routes.password_reset_token', [], $locale),
                'Auth\ResetPassword@showResetForm'
            )->name(i18nRouteNames($locale, 'password.reset'));
            $r->post('password/reset', 'Auth\ResetPassword@reset')->name('password.reset.do');
            $r->get(trans('core::tr.routes.contact', [], $locale), 'Frontend@contact')
                ->name(i18nRouteNames($locale, 'contact'));
            $r->post('contact/send', 'Frontend@sendContactEmail')->name('contact.send');

            $r->get(trans('core::tr.routes.search', [], $locale), 'Search@get')
                ->name(i18nRouteNames($locale, 'search'));

            $r->post('email/subscribe/newsletter', 'Frontend@newsletterSubscribe')
                ->name('subscribe_newsletter');

            $r->get(trans('core::tr.routes.privacy', [], $locale), 'Frontend@privacy')
                ->name(i18nRouteNames($locale, 'privacy'));
            $r->get(trans('core::tr.routes.terms_service', [], $locale), 'Frontend@termsOfService')
                ->name(i18nRouteNames($locale, 'terms.service'));
        };

    }

    public static function auth($locale)
    {
        return function (Router $r) use ($locale) {
            $r->post('logout', 'Auth\Login@logout')->name('logout');
            $r->get(trans('core::tr.routes.settings_profile', [], $locale), 'Settings\Profile@edit')
                ->name(i18nRouteNames($locale, 'profile'));
            $r->post('settings/profile/update', 'Settings\Profile@update')
                ->name('profile.update');
            $r->get(trans('core::tr.routes.settings_notifications', [], $locale), 'Settings\Notifications@edit')
                ->name(i18nRouteNames($locale, 'notifications'));
            $r->get(trans('core::tr.routes.settings_account', [], $locale), 'Settings\Account@edit')
                ->name(i18nRouteNames($locale, 'account'));
        };
    }

    public static function misc()
    {
    }

}