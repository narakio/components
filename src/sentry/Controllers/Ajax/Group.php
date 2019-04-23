<?php namespace Naraki\Sentry\Controllers\Ajax;

use Illuminate\Http\Response;
use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Core\Models\Entity;
use Naraki\Permission\Events\PermissionEntityUpdated;
use Naraki\Permission\Facades\Permission;
use Naraki\Sentry\Facades\Group as GroupProvider;
use Naraki\Sentry\Requests\Admin\CreateGroup;
use Naraki\Sentry\Requests\Admin\UpdateGroup;

class Group extends Controller
{
    /**
     * @return array
     */
    public function index()
    {
        $filter = GroupProvider::newFilter();
        return [
            'table' => GroupProvider::select([
                'groups.group_id',
                'group_name',
                'group_slug',
                'group_mask',
                'permission_mask',
                \DB::raw('count(group_members.user_id) as member_count')
            ])->leftGroupMember()->entityType()
                ->permissionRecord()
                ->permissionStore()
                ->permissionMask($this->user->getAttribute('entity_type_id'))->groupBy([
                    'groups.group_id',
                    'group_name',
                    'permission_mask',
                ])
                ->filter($filter)->paginate(10),
            'columns' => (new \Naraki\Sentry\Models\Group)->getColumnInfo([
                'group_name' => (object)[
                    'name' => trans('nk::jsb.db.group_name'),
                    'width' => '60%'
                ],
                'group_mask' => (object)[
                    'name' => trans('nk::jsb.db.group_mask'),
                    'width' => '20%'
                ]
            ], $filter),
            'member_count' => trans('nk::jsb.db.member_count'),

        ];

    }

    /**
     * @param string $groupSlug
     * @return array
     */
    public function edit($groupSlug)
    {
        $groupDb = GroupProvider::getOneBySlug($groupSlug,
            ['group_slug', 'group_name', 'group_mask', 'entity_type_id'])
            ->scopes(['entityType'])->first();
        if (is_null($groupDb)) {
            return response(trans('error.http.500.user_not_found'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $group = $groupDb->toArray();
        $entityType_id = $group['entity_type_id'];
        unset($group['entity_type_id']);
        return [
            'group' => $group,
            'permissions' => Permission::getRootAndGroupPermissions($entityType_id)
        ];
    }

    /**
     * @param string $groupName
     * @param \Naraki\Sentry\Requests\Admin\UpdateGroup $request
     * @return \Illuminate\Http\Response
     */
    public function update(
        $groupName,
        UpdateGroup $request
    ) {
        $group = GroupProvider::updateOneBySlug($groupName, $request->all());
        $permissions = $request->getPermissions();

        if (!is_null($permissions)) {
            Permission::updateIndividual(
                $permissions,
                $group->getAttribute('entity_type_id'),
                Entity::GROUPS);
            event(new PermissionEntityUpdated);
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $groupName
     * @return \Illuminate\Http\Response
     */
    public function destroy($groupName)
    {
        GroupProvider::deleteBySlug($groupName);
        event(new PermissionEntityUpdated);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Naraki\Permission\Contracts\Permission|\Naraki\Core\Support\Providers\\Permission $permissionProvider
     * @return array
     */
    public function add()
    {
        return [
            'permissions' => Permission::getRootAndGroupPermissions()
        ];
    }

    /**
     * @param \Naraki\Sentry\Requests\Admin\CreateGroup $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateGroup $request)
    {

        $group = GroupProvider::createOne($request->all());
        $permissions = $request->getPermissions();

        if (!is_null($permissions)) {
            Permission::updateIndividual(
                $permissions,
                intval($group->getAttribute('entity_type_id')),
                Entity::GROUPS
            );
        }
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
