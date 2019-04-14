<?php namespace Naraki\Sentry\Providers;

use Naraki\Core\Contracts\RawQueries;
use Naraki\Core\EloquentProvider;
use Naraki\Core\Traits\Filterable;
use Naraki\Permission\Events\PermissionEntityUpdated;
use Naraki\Sentry\Contracts\Group as GroupInterface;
use Naraki\Sentry\Models\GroupMember;

/**
 * @method \Naraki\Sentry\Models\Group createModel(array $attributes = [])
 * @method \Naraki\Sentry\Models\Group getOne($id, $columns = ['*'])
 */
class Group extends EloquentProvider implements GroupInterface
{
    use Filterable;
    /**
     * @var string
     */
    protected $model = \Naraki\Sentry\Models\Group::class;
    /**
     * @var string
     */
    protected $filter = \Naraki\Sentry\Models\Filters\Group::class;

    /**
     * @param string $slug
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOneBySlug($slug, $columns = ['*'])
    {
        return $this->select($columns)->where('group_slug', '=', $slug);
    }

    /**
     * @param string $slug
     * @param array $data
     * @return \Naraki\Sentry\Models\Group
     */
    public function updateOneBySlug($slug, $data)
    {
        return $this->updateOneGroup('group_slug', $slug, $data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return \Naraki\Sentry\Models\Group
     */
    public function updateOneById($id, $data)
    {
        return $this->updateOneGroup($this->createModel()->getKeyName(), $id, $data);
    }

    /**
     * @param string $field
     * @param string $value
     * @param array $data
     * @return \Naraki\Sentry\Models\Group
     */
    public function updateOneGroup($field, $value, $data)
    {
        $group = $this->buildOneWithScopes(
            ['group_id', 'entity_type_id'],
            ['entityType'],
            [[$field, $value]])
            ->first();

        $this->build()->where($field, $value)
            ->update($this->filterFillables($data));
        return $group;
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOne($data)
    {
        $data['group_slug'] = slugify($data['group_name']);
        $this->createModel($data)->save();
        event(new PermissionEntityUpdated);
        return $this->buildOneWithScopes(
            ['entity_type_id'],
            ['entityType'],
            [['group_slug', $data['group_slug']]])
            ->first();
    }

    /**
     * @param string $slug
     */
    public function deleteBySlug(string $slug)
    {
        $this->build()->where('group_slug', '=', $slug)->delete();
        event(new PermissionEntityUpdated);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getMembers(string $slug)
    {
        list($select, $scopes, $wheres) = [
            [\DB::raw('count(group_members.user_id) as c')],
            ['groupMember', 'user'],
            [['group_slug', '=', $slug]]
        ];
        $count = $this->buildOneWithScopes($select, $scopes, $wheres)->pluck('c')->pop();

        if ($count > 25) {
            return ['count' => $count];
        } else {
            return [
                'count' => $count,
                'users' => $this->buildOneWithScopes(['full_name as text', 'username as id'], $scopes, $wheres)
                    ->orderBy('last_name', 'asc')->get()
            ];
        }
    }

    /**
     * @param $slug
     * @param string $search
     * @param int $limit
     * @return \Naraki\Sentry\Models\Group[]
     */
    public function searchMembers(string $slug, $search, $limit = 10)
    {
        return $this->buildOneWithScopes(
            ['full_name as text', 'username as id'],
            ['groupMember', 'user'],
            [
                ['group_slug', strip_tags($slug)],
                ['full_name', 'like', sprintf('%%%s%%', strip_tags($search))]
            ])
            ->limit($limit)->get();
    }

    /**
     * @param $slug
     * @param \StdClass $data
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateMembers($slug, $data)
    {
        if (empty($data->added) && empty($data->removed)) {
            return;
        }

        $groupId = $this->select('group_id')
            ->where('group_slug', '=', $slug)
            ->pluck('group_id')->first();

        if (!empty($data->added) && is_int($groupId)) {
            /** @var \Naraki\Core\Support\Database\MysqlRawQueries $rawQueries */
            $rawQueries = app()->make(RawQueries::class);
            $userIds = $rawQueries->getUsersInArrayNotInGroup($data->added, $slug);
            if (is_int($groupId)) {
                $groupMembers = [];
                foreach ($userIds as $userId) {
                    $groupMembers[] = ['user_id' => $userId, 'group_id' => $groupId];
                }
                GroupMember::query()->insert($groupMembers);
            }
        }

        if (!empty($data->removed) && is_int($groupId)) {
            $userIds = (new \Naraki\Sentry\Models\User)->newQueryWithoutScopes()->select(['user_id'])
                ->whereIn('username', $data->removed)
                ->pluck('user_id')->toArray();
            if (!is_null($userIds) && !empty($userIds)) {
                GroupMember::query()->whereIn('user_id', $userIds)->delete();
            }
        }
    }

    /**
     * @param int $userId
     * @param array $groups
     */
    public function updateSingleMemberGroups($userId, $groups)
    {
        $existingUserGroups = GroupMember::query()->select(['groups.group_id', 'group_slug'])
            ->scopes(['group'])->where('user_id', $userId)->pluck('group_id', 'group_slug');
        $groupInfo = $this->select(['group_id', 'group_slug'])->pluck('group_id', 'group_slug');

        $groupsToAdd = $groupsToRemove = [];
        foreach ($groups as $group) {
            if (!isset($existingUserGroups[$group])) {
                $groupsToAdd[] = ['user_id' => $userId, 'group_id' => $groupInfo[$group]];
            }
        }

        $groupsFlipped = array_flip($groups);
        foreach ($existingUserGroups as $existing => $id) {
            if (!isset($groupsFlipped[$existing])) {
                if (isset($groupInfo[$existing])) {
                    $groupsToRemove[] = $groupInfo[$existing];
                }
            }
        }

        if(!empty($groupsToAdd)){
            GroupMember::query()->insert($groupsToAdd);
        }

        if(!empty($groupsToRemove))
        {
            GroupMember::query()->whereIn('group_id',$groupsToRemove)
                ->where('user_id',$userId)
                ->delete();
        }

    }
}