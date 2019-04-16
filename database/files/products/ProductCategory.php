<?php

class ProductCategory
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
     * @var string
     */
    private $language;

    /**
     *
     * @param int $id
     * @param string $name
     * @param string $language
     */
    public function __construct(int $id, string $name, string $language = 'fr')
    {
        $this->id = $id;
        $this->name = $name;
        $this->setLanguage($language);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        if (preg_match('/(en|fr)/', $language)) {
            $this->language = $language;
        } else {
            $this->language = 'fr';
        }
    }

    public function __get($name)
    {
        return $this->{$name};
    }


}