<?php

class Image
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $type;
    /**
     * @var int
     */
    private $width;
    /**
     * @var int
     */
    private $height;
    /**
     * @var int
     */
    private $rev;

    /**
     *
     * @param string $id
     * @param string $type
     * @param int $width
     * @param int $height
     * @param int $rev
     */
    public function __construct(string $id, string $type, int $width=0, int $height=0, int $rev=0)
    {
        $this->id = $id;
        $this->type = $type;
        $this->width = $width;
        $this->height = $height;
        $this->rev = $rev;
    }

    public function __get($name)
    {
        return $this->{$name};

    }
}