<?php namespace Naraki\Forum\Support\Trees;

use Carbon\Carbon;

class Post
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var array
     */
    private $data;
    /**
     * @var array
     */
    private $children = [];
    private static $sortColumn;
    private static $order;

    /**
     *
     * @param string $id
     * @param array $data
     */
    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @param int $level
     * @return \stdClass
     */
    public function toArray($level = 1): \stdClass
    {
        $ar = $this->getNewRoot($this);

        $level++;
        $children = [];
        foreach ($this->children as $child) {
            $children[] = $child->toArray($level);
        }
        $column = static::$sortColumn;
        $order = static::$order;

        usort($children, function ($a, $b) use ($column, $order) {
            if ($order === 'asc') {
                return strcmp($a->{$column}, $b->{$column});
            } else {
                return strcmp($b->{$column}, $a->{$column});
            }
        });
        foreach ($children as $child) {
            $child->updated_at = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $child->updated_at)->diffForHumans();
        }
        $ar->children = $children;
        return $ar;
    }

    /**
     * @param self $node
     * @return object
     */
    private function getNewRoot($node): \stdClass
    {
        $data = $node->getData();
        unset($data['id']);
        $data['children'] = [];
        return (object)$data;
    }

    /**
     * @param string $id
     * @param $data
     * @return self
     */
    public function addChild($id, $data): self
    {
        $new = new static($id, $data);
        $new::$sortColumn = static::$sortColumn;
        $new::$order = static::$order;
        array_push($this->children, $new);
        return $new;
    }

    /**
     * @param array $posts
     * @return array
     */
    public static function getTree($posts, $sortColumn, $order): array
    {
        if (empty($posts)) {
            return [];
        }

        static::$sortColumn = $sortColumn;
        static::$order = $order;
        return static::makeTree($posts);
    }

    /**
     * @param $posts
     * @return array
     */
    private static function makeTree($posts)
    {
        $level = 0;
        $root = '';
        $l = [];
        foreach ($posts as $node) {
            $lvl = $node['lvl'];
            $id = $node['slug'];
            switch (true) {
                case ($lvl === 0):
                    $level = $lvl;
                    $root = $id;
                    $l[$root][$level] = new static($id, $node);
                    break;
                case ($lvl === $level):
                    $l[$root][$lvl] = $l[$root][$lvl - 1]->addChild($id, $node);
                    break;
                case ($lvl > $level):
                    $l[$root][$lvl] = $l[$root][$level]->addChild($id, $node);
                    $level = $lvl;
                    break;
                case ($lvl < $level):
                    $l[$root][$lvl] = $l[$root][$lvl - 1]->addChild($id, $node);
                    $level = $lvl;
                    break;
            }
        }
        $tree = [];
        $column = static::$sortColumn;
        $order = static::$order;
        usort($l, function ($a, $b) use ($column, $order) {
            if ($order === 'asc') {
                return strcmp($a[0]->{$column}, $b[0]->{$column});
            } else {
                return strcmp($b[0]->{$column}, $a[0]->{$column});
            }
        });
        foreach ($l as $node) {
            $tree[] = $node[0]->toArray();
        }
        return $tree;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    private function sort($column)
    {

    }
}