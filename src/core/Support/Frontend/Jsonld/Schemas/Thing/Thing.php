<?php namespace Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing;

use Naraki\Core\Support\Frontend\Jsonld\Schema;

class Thing extends Schema
{
    protected $alternateName;
    protected $description;
    protected $image;
    protected $name;
    protected $sameAs;
    protected $url;
    protected $potentialAction;
    protected $mainEntityOfPage;

    public function setPotentialAction($values, $class)
    {
        return $this->setValuesDefault(
            sprintf('%s\Action\%s', __NAMESPACE__, $class), $values);
    }

    public function setMainEntityOfPage($values, $class)
    {
        return $this->setValuesDefault(
            sprintf('%s\CreativeWork\%s', __NAMESPACE__, $class), $values);
    }

}