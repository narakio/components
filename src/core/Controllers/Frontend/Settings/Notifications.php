<?php namespace Naraki\Core\Controllers\Frontend\Settings;

use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Mail\Models\EmailList;
use Naraki\Core\Support\Frontend\Breadcrumbs;

class Notifications extends Controller
{
    /**
     * @var \Naraki\Mail\Contracts\Email|\Naraki\Mail\Providers\Email $emailRepo
     */
    private $emailRepo;

    public function __construct()
    {
        $this->emailRepo = app(\Naraki\Mail\Contracts\Email::class);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {

        $user = auth()->user();
        return view('core::frontend.site.settings.panes.notifications', [
            'user' => $user,
            'title' => trans('pages.profile.settings_title'),
            'breadcrumbs' => Breadcrumbs::render([
                [
                    'label' => trans('titles.routes.notifications'),
                    'url' => route_i18n('notifications')
                ]
            ]),
            'mailing_lists' => EmailList::getList(),
            'subscribed' => array_flip(
                $this->emailRepo->subscriber()
                    ->buildAllUser(
                $user->getAttribute('person_id'),
                ['email_lists.email_list_id']
                    )->pluck('email_list_id')->toArray())
        ]);
    }

}