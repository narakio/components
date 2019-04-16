<?php

class Ingredient
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $rank;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $language;

    /**
     *
     * @param int $id
     * @param int $rank
     * @param string $name
     * @param string $language
     */
    public function __construct(int $id = 0, int $rank = 0, string $name = null, string $language = null)
    {
        $this->id = $id;
        $this->rank = $rank;
        $this->name = $name;
        $this->language = $language;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank)
    {
        $this->rank = $rank;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }

    public function getTag()
    {
        return sprintf('%s:$s', $this->language, slugify($this->name));
    }

    public function hasName()
    {
        return (!is_null($this->name));
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

}