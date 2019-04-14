<?php namespace Naraki\Sentry\Controllers\Ajax;

use Naraki\Permission\Events\PermissionEntityUpdated;
use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Sentry\Requests\Admin\UpdateMember;
use Naraki\Sentry\Providers\Group as GroupProvider;
use Illuminate\Http\Response;

class GroupMember extends Controller
{

    /**
     * @param string $slug
     * @param \Naraki\Sentry\Contracts\Group|\Naraki\Sentry\Providers\Group $groupProvider
     * @return \Illuminate\Http\Response
     */
    public function index($slug, GroupProvider $groupProvider)
    {
        return response($groupProvider->getMembers($slug), Response::HTTP_OK);
    }

    /**
     * @param string $slug
     * @param string $search
     * @param \Naraki\Sentry\Contracts\Group|\Naraki\Sentry\Providers\Group $groupProvider
     * @return \Illuminate\Http\Response
     */
    public function search($slug, $search, GroupProvider $groupProvider)
    {
        return response($groupProvider->searchMembers($slug, $search), Response::HTTP_OK);
    }

    /**
     * @param string $slug
     * @param \Naraki\Sentry\Contracts\Group|\Naraki\Sentry\Providers\Group $groupProvider
     * @param \Naraki\Sentry\Requests\Admin\UpdateMember $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function update($slug, GroupProvider $groupProvider, UpdateMember $request)
    {
        $groupProvider->updateMembers($slug, (object)$request->input());
        event(new PermissionEntityUpdated);

        return response(null, Response::HTTP_NO_CONTENT);
    }


}
