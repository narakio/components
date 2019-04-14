<?php namespace Naraki\Sentry\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Core\Models\Entity;
use Naraki\Media\Facades\Media as MediaProvider;
use Naraki\Permission\Events\PermissionEntityUpdated;
use Naraki\Permission\Facades\Permission;
use Naraki\Sentry\Events\UserRegistered;
use Naraki\Sentry\Facades\Group as GroupProvider;
use Naraki\Sentry\Facades\User as UserProvider;
use Naraki\Sentry\Requests\Admin\UpdateUser;

class User extends Controller
{
    /**
     * @return array
     */
    public function index()
    {
        $userFilter = UserProvider::newFilter();
        UserProvider::setStoredFilter(Entity::USERS, $this->user->getKey(), $userFilter);
        $usersDb = UserProvider::select([
            \DB::raw('null as selected'),
            'username',
            'full_name',
            'email',
            'created_at as created_ago',
            'created_at'
        ])
            ->where('username', '!=', $this->user->getAttribute('username'))
            ->filter($userFilter);

        if (!$userFilter->hasFilters()) {
            $usersDb->orderBy('users.user_id', 'desc');
        }

        $groups = (clone $usersDb)->select('group_name')
            ->where('groups.group_id', '>', 1)
            ->groupBy('group_name');
        if (!$userFilter->hasFilter('group')) {
            $groups->groupMember();
        }
        $usersPaginated = $usersDb->paginate(25);
        unset($usersDb);

        $userPermissions = UserProvider::buildWithScopes([
            'username',
            'permission_mask as acl'
        ], [
            'entityType',
            'permissionRecord',
            'permissionStore',
            'permissionMask' => $this->user->getEntityType(),
        ])
            ->whereIn('username', $usersPaginated->pluck('username')->all())
            ->orderBy('users.user_id', 'asc')
            ->get();
        $permissions = [];
        foreach ($userPermissions as $permission) {
            $permissions[$permission->getAttribute('username')] = $permission->getAttribute('acl');
        }
        foreach ($usersPaginated as $user) {
            //if the currently logged in user does not have permissions on this particular user,
            //the acl value will be 0. We need to distinguish that case from users not having permissions because
            //no permissions are assigned or they're not part of groups
            //So when we don't have permissions, we set the bitmask the 16 so that all binary comparisons fail
            //If no permissions exist, we set the bitmask to 0.
            $user->setAttribute('acl',
                isset($permissions[$user->getAttribute('username')])
                    ? ($permissions[$user->getAttribute('username')] > 0)
                    ? $permissions[$user->getAttribute('username')]
                    : 16
                    : 0
            );
        }

        return [
            'table' => $usersPaginated,
            'groups' => $groups->pluck('group_name'),
            'sorted' => $userFilter->getFilter('sortBy'),
            'columns' => UserProvider::createModel()->getColumnInfo([
                'full_name' => (object)[
                    'name' => trans('js-backend.db.full_name'),
                ],
                'email' => (object)[
                    'name' => trans('js-backend.general.email'),
                ],
                'created_ago' => (object)[
                    'name' => trans('js-backend.db.user_created_at'),
                ]
            ], $userFilter)
        ];
    }

    /**
     * @return array
     */
    public function add()
    {
        return [
            'user' => [],
            'nav' => [],
            'intended' => null,
            'groups' => GroupProvider::buildWithScopes([
                    'group_name as name',
                    'group_slug as slug',
                    \DB::raw('false as isMember'),
                ], [
                    'leftGroupMember',
                    'entityType',
                    'permissionRecord',
                    'permissionStore',
                    'permissionMask' => $this->user->getAttribute('entity_type_id')
                ])
                ->groupBy([
                    'group_slug',
                ])->get(),
            'media' => []
        ];
    }

    /**
     * @param \Naraki\Sentry\Requests\Admin\UpdateUser $request
     * @return \Illuminate\Http\Response
     */
    public function create(UpdateUser $request)
    {
        $user = UserProvider::createOne($request->all(), true);

        if ($request->hasGroups()) {
            GroupProvider::updateSingleMemberGroups($user->getKey(), $request->getGroups());
        }

        if ($request->hasPermissions()) {
            Permission::updateIndividual($request->getPermissions(), $user->getEntityType(), Entity::USERS);
            Permission::cacheUserPermissions($user->getEntityType());
        }

        if ($request->hasPermissions() || $request->hasGroups()) {
            event(new PermissionEntityUpdated);
        }
        event(new UserRegistered($user));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $username
     * @return mixed
     */
    public function edit($username)
    {
        $filter = UserProvider::getStoredFilter(Entity::USERS, $this->user->getKey());
        $nav = [];
        if (!is_null($filter)) {
            $userSiblings = UserProvider::buildWithScopes([
                'username',
            ], [
                'entityType',
                'permissionRecord',
                'permissionStore',
                'permissionMask' => $this->user->getEntityType()
            ])->where('username', '!=', $this->user->getAttribute('username'))
                ->filter($filter)
                ->pluck('username')->all();

            $total = count($userSiblings);
            $index = array_search($username, $userSiblings);
            $nav = array(
                'total' => $total,
                'idx' => ($index + 1),
                'first' => $userSiblings[0],
                'last' => $userSiblings[($total - 1)],
                'prev' => ($userSiblings[$index - 1] ?? null),
                'next' => ($userSiblings[$index + 1] ?? null)
            );
        }
        $user = UserProvider::buildOneByUsername($username,
            [
                'first_name',
                'last_name',
                'email',
                'username',
                'full_name',
                'entity_type_id'
            ])->scopes(['entityType'])->first();
        if (is_null($user)) {
            return response(trans('error.http.500.user_not_found'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $entityTypeId = $user['entity_type_id'];
        $media = MediaProvider::image()
            ->buildImages($entityTypeId, null, true)
            ->first();

        $allVisibleGroups = GroupProvider::buildWithScopes([
                'group_name as name',
                'group_slug as slug',
            ], [
                'leftGroupMember',
                'entityType',
                'permissionRecord',
                'permissionStore',
                'permissionMask' => $this->user->getAttribute('entity_type_id')
            ])
            ->groupBy([
                'group_slug',
            ])->get();
        $userGroupsDb = UserProvider::buildOneWithScopes([
            'groups.group_id as id',
            'group_slug as slug'
        ], ['groupMember'], [['username', $username]])->pluck('id', 'slug');
        $userGroups = $userGroupsDb->toArray();
        foreach ($allVisibleGroups as $grp) {
            if (isset($userGroups[$grp->getAttribute('slug')])) {
                $grp->setAttribute('isMember', true);
            } else {
                $grp->setAttribute('isMember', false);
            }
        }

        unset($user['entity_type_id']);
        return [
            'user' => $user->toArray(),
            'permissions' => Permission::getAllUserPermissions($entityTypeId),
            'groups' => $allVisibleGroups->toArray(),
            'nav' => $nav,
            'intended' => null,
            'media' => $media
        ];
    }

    /**
     * @param $username
     * @param \Naraki\Sentry\Requests\Admin\UpdateUser $request
     * @return \Illuminate\Http\Response
     */
    public function update(
        $username,
        UpdateUser $request
    ) {
        $user = UserProvider::updateOneByUsername($username, $request->all());

        if ($request->hasGroups()) {
            GroupProvider::updateSingleMemberGroups($user->getKey(), $request->getGroups());
        }

        if ($request->hasPermissions()) {
            Permission::updateIndividual($request->getPermissions(), $user->getEntityType(), Entity::USERS);
            Permission::cacheUserPermissions($user->getEntityType());
        }

        if ($request->hasPermissions() || $request->hasGroups()) {
            event(new PermissionEntityUpdated);
        }

        event(new UserRegistered($user));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $search
     * @param $limit
     * @return \Illuminate\Http\Response
     */
    public function search($search, $limit)
    {
        return response(
            UserProvider::search(
                preg_replace('/[^\w\s\-\']/', '', strip_tags($search)),
                $this->user->getAttribute('entity_type_id'),
                intval($limit)
            )->get(), Response::HTTP_OK);
    }

    /**
     * @param string $username
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy($username)
    {
        UserProvider::deleteByUsername($username);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function batchDestroy(Request $request)
    {
        UserProvider::deleteByUsername($request->only('users'));
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function profile()
    {
        return response([
            'user' => $this->user->only([
                'first_name',
                'last_name',
                'email',
                'username',
                'full_name',
            ]),
            'avatars' => UserProvider::getAvatars($this->user->getKey())
        ], Response::HTTP_OK);
    }


}
