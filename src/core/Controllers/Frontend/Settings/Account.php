<?php namespace Naraki\Core\Controllers\Frontend\Settings;

use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Core\Support\Frontend\Breadcrumbs;

class Account extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $user = auth()->user();
        return view('core::frontend.site.settings.panes.account', [
            'user' => $user,
            'title' => trans('pages.profile.settings_title'),
            'breadcrumbs' => Breadcrumbs::render([
                ['label' => trans('titles.routes.account'), 'url' => route_i18n('account')]
            ])
        ]);
    }

}