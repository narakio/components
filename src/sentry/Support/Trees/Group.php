<?php namespace Naraki\Core\Support\Trees;

class Group
{
    /**
     * @var Group[]
     */
    private $children = [];
    /**
     * @var Group[]
     */
    private $siblings = [];
    /**
     * @var Group
     */
    private $parent = null;
    /**
     * @var string
     */
    private $name = null;
    /**
     * @var integer
     */
    private $index = null;


    /**
     *
     * @param string $name
     * @param integer $index
     */
    public function __construct($name = null, $index = null)
    {
        $this->name = $name;
        $this->index = $index;
    }

    /**
     * @param $index
     * @return \Naraki\Core\Support\Trees\Group|bool
     */
    public function findAtIndex($index)
    {
        if (isset($this->siblings[$index])) {
            return $this->siblings[$index];
        } elseif (isset($this->children[$index])) {
            return $this->children[$index];
        } elseif (!empty($this->children)) {
            reset($this->children);
            return $this->children[key($this->children)]->findAtIndex($index);
        }
        return false;
    }

    /**
     * @param \Naraki\Core\Support\Trees\Group $parent
     */
    public function addParent(Group $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param \Naraki\Core\Support\Trees\Group $sibling
     */
    public function addSibling(Group $sibling)
    {
        $sibling->addParent($this->parent);
        $this->siblings[$sibling->getIndex()] = $sibling;
    }

    /**
     * @param \Naraki\Core\Support\Trees\Group $child
     * @return \Naraki\Core\Support\Trees\Group
     */
    public function addChild(Group $child):Group
    {
        $this->children[$child->getIndex()] = $child;
        $child->addParent($this);
        if (!empty($this->siblings)) {
            foreach ($this->siblings as $sibling) {
                $sibling[$child->getIndex()] = $child;
            }
        }
        return $child;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }
}