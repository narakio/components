<?php

class Packager
{
    /**
     * @var string
     */
    private $name;
    /**
     * Approval number
     *
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $emb;
    /**
     * @var string
     */
    private $siret;
    /**
     * @var string
     */
    private $address;
    /**
     * @var string
     */
    private $postcode;
    /**
     * @var string
     */
    private $town;
    /**
     * @var array
     */
    private $category = [];
    /**
     * @var array
     */
    private $activity = [];
    /**
     * @var string
     */
    private $species;
    /**
     * @var
     */
    private $id;

    /**
     *
     * @param string $code
     * @param int $id
     * @param string $name
     * @param string $siret
     * @param string $address
     * @param string $postcode
     * @param string $town
     * @param array $category
     * @param array $activity
     * @param string $species
     */
    public function __construct(
        string $code,
        int $id,
        string $name = null,
        string $siret = null,
        string $address = null,
        string $postcode = null,
        string $town = null,
        array $category = [],
        array $activity = [],
        string $species = null
    ) {
        $this->name = $name;
        $this->code = $code;
        if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $code)) {
            $this->emb = sprintf('FR %s EC', $code);
        } else {
            $this->emb = $code;
        }
        $this->siret = $siret;
        $this->address = $address;
        $this->postcode = $postcode;
        $this->town = $town;
        $this->category = $category;
        $this->activity = $activity;
        $this->species = $species;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmb(): string
    {
        return $this->emb;
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function updateCategory($s)
    {
        if (!empty($s) && !is_null($s)) {
            $this->category[] = $s;
        }
    }

    public function updateActivity($s)
    {
        if (!empty($s) && !is_null($s)) {
            $this->activity[] = $s;
        }
    }

}