<?php namespace Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing;

use Naraki\Core\Support\Frontend\Jsonld\Schemas\GeoCoordinates;

class Place extends Thing
{
    protected $geo;

    public function setGeo($values)
    {
        return $this->setValuesDefault(GeoCoordinates::class, $values);
    }

}