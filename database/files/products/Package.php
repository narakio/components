<?php

class Package
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
     *
     * @param int $id
     * @param $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }


    public function __get($name)
    {
        return $this->{$name};
    }


}