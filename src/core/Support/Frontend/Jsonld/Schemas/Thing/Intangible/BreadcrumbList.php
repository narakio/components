<?php namespace Naraki\Core\Support\Frontend\Jsonld\Schemas\Thing\Intangible;

class BreadcrumbList extends Intangible
{
    protected $itemListElement;

    public function setItemListElement($values)
    {
        $properties = [];
        foreach ($values as $value) {
            $properties[] = (new ListItem($value))->getProperties();
        }
        return $properties;
    }
}