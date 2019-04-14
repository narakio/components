<?php namespace Naraki\Core\Support\Trees;

use Naraki\Core\Traits\Enumerable;

class GroupHierarchy
{
    use Enumerable;

    const root = 1;
    const superadmins = 2;
    const admins = 2000;
    const users = 5000;

    public static function getTree($groups)
    {
        $tree = new GroupHierarchy();
        return $tree->makeTree($groups);
    }

    private function makeTree($groups)
    {
        $tree = new Group();
        $tmp = $tree;
        $currentIndex = 0;
        foreach ($groups as $group) {
            if ($group->group_mask > $currentIndex) {
                $child = $tmp->addChild(new Group($group->group_name, $group->group_mask));
                $tmp = $child;
            } else {
                $tmp->addSibling(new Group($group->group_name, $group->group_mask));
            }
            $currentIndex = $group->group_mask;
        }
        return $tree;
    }

}