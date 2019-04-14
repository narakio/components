<?php namespace Naraki\Core\Controllers\Frontend\Settings;

use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Sentry\Requests\Frontend\UpdateUser;
use Naraki\Sentry\Jobs\UpdateUserElasticsearch;
use Naraki\Core\Support\Frontend\Breadcrumbs;
use Naraki\Sentry\Facades\User as UserProvider;

class Profile extends Controller
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
        return view('core::frontend.site.settings.panes.profile', [
            'user' => $user,
            'title' => trans('pages.profile.settings_title'),
            'breadcrumbs' => Breadcrumbs::render([
                ['label' => trans('titles.routes.profile'), 'url' => route_i18n('profile')]
            ]),
            'avatars' => UserProvider::getAvatars($user->getKey())
        ]);
    }

    /**
     * @param \Naraki\Sentry\Requests\Frontend\UpdateUser $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUser $request)
    {
        $user = auth()->user();
        if (!empty($request->all())) {
            $rest = $request->except(['notifications']);
            if (!empty($rest)) {
                if (isset($rest['password'])) {
                    $rest['password'] = bcrypt($rest['password']);
                }
                UserProvider::updateOneByUsername(
                    $user->getAttribute('username'),
                    $rest
                );
                $this->dispatch(new UpdateUserElasticsearch(
                        UpdateUserElasticsearch::WRITE_MODE_UPDATE,
                        $user->getKey())
                );
            }
        }
        $lists = $request->input('notifications');
        if (!empty($lists)) {
            $lists = array_flip($lists);
        } else {
            $lists = [];
        }
        $this->emailRepo->subscriber()->addUserToLists(
            $user->getAttribute('person_id'),
            $lists
        );
        return back()->with(
            'msg',
            ['type' => 'success', 'title' => trans('messages.profile_update_success')]
        );

    }

}