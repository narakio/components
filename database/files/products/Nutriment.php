<?php

class Nutriment
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string;
     */
    private $quantity;

    /**
     *
     * @param int $id
     * @param string $name
     * @param string $quantity
     */
    public function __construct(int $id, string $name, string $quantity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->quantity = $quantity;
    }

    public function __get($name)
    {
        return $this->{$name};

    }

}