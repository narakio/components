<?php namespace Naraki\Permission\Support;

class PermissionData
{
    /**
     * @var int
     */
    private $target;
    /**
     * @var int
     */
    private $holder;
    /**
     * @var int
     */
    private $mask;

    /**
     *
     * @param \stdClass $target
     * @param \stdClass $holder
     * @param int $mask
     */
    public function __construct($target, $holder, $mask=0)
    {
        $this->target = $target;
        $this->holder = $holder;
        $this->mask = $mask;
    }

    public function setTarget($value = null)
    {
        $this->target->entity_type_id = $value;
    }

    public function setMask($value = null)
    {
        $this->mask = $value;
    }

    /**
     * @return int
     */
    public function getTarget()
    {
        return $this->target->entity_type_id;
    }

    /**
     * @return int
     */
    public function getHolder()
    {
        return $this->holder->entity_type_id;
    }

    /**
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }


}