<?php namespace Naraki\Blog\Support\Trees;

use Naraki\Blog\Models\Views\BlogCategoryTree;
use Naraki\Blog\Providers\Category as BlogCategoryProvider;

class Category
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var array
     */
    private $children = [];
    /**
     * @var string
     */
    private $id;
    /**
     * @var bool
     */
    private $selected;

    /**
     *
     * @param string $label
     * @param string $id
     * @param bool $selected
     */
    public function __construct($label, $id, $selected = false)
    {
        $this->label = $label;
        $this->id = $id;
        $this->selected = $selected;
    }

    /**
     * @param int $level
     * @return \stdClass
     */
    public function toArray($level = 1)
    {
        $ar = $this->getNewRoot($this, $level);

        $level++;
        foreach ($this->children as $child) {
            $ar->children[] = $child->toArray($level);
        }
        return $ar;
    }

    /**
     * @param \Naraki\Blog\Support\Trees\Category $node
     * @param int $level
     * @return object
     */
    private function getNewRoot($node, $level)
    {
        return (object)[
            'label' => $node->getLabel(),
            'open' => ($level > 2 ? false : true),
            'selected' => $node->isSelected(),
            'mode' => 1,
            'id' => $node->getId(),
            'children' => []
        ];
    }

    /**
     * @param string $label
     * @param string $id
     * @param bool $selected
     * @return \Naraki\Blog\Support\Trees\Category
     */
    public function addChild($label, $id, $selected)
    {
        $new = new static($label, $id, $selected);
        array_push($this->children, $new);
        return $new;
    }

    /**
     * @return array
     */
    public static function getTree()
    {
        return static::makeTree()->tree;
    }

    /**
     * @param int $postId
     * @return array|\stdClass
     */
    public static function getTreeWithSelected($postId = null)
    {
        return static::makeTree($postId);
    }

    /**
     * @param int $postId
     * @return array|\stdClass
     */
    private static function makeTree($postId = null)
    {
        $selectedCategories = [];
        $f = BlogCategoryTree::query()
            ->select(['label', 'lvl', 'id'])->get()->toArray();
        if (!is_null($postId)) {
            $selectedCategories = array_flip((new BlogCategoryProvider())->getSelected($postId));
        }
        $level = 0;
        $root = '';
        $l = [];
        foreach ($f as $node) {
            $lvl = $node['lvl'];
            $label = $node['label'];
            $id = $node['id'];
            $selected = (!is_null($postId) && isset($selectedCategories[$node['id']])) ? true : false;
            switch (true) {
                case ($lvl === 0):
                    $level = $lvl;
                    $root = $id;
                    $l[$root][$level] = new static($label, $id, $selected);
                    break;
                case ($lvl === $level):
                    $l[$root][$lvl] = $l[$root][$lvl - 1]->addChild($label, $id, $selected);
                    break;
                case ($lvl > $level):
                    $l[$root][$lvl] = $l[$root][$level]->addChild($label, $id, $selected);
                    $level = $lvl;
                    break;
                case ($lvl < $level):
                    $l[$root][$lvl] = $l[$root][$lvl - 1]->addChild($label, $id, $selected);
                    $level = $lvl;
                    break;
            }
        }
        $tree = [];
        foreach ($l as $node) {
            $tree[] = $node[0]->toArray();
        }
        return (object)['tree' => $tree, 'categories' => array_flip($selectedCategories)];
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }


}