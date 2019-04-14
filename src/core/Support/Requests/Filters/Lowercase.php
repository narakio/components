<?php namespace Naraki\Core\Support\Requests\Filters;

class Lowercase
{
    /**
     *  Lowercase the given string.
     *
     *  @param  string  $value
     *  @return string
     */
    public function apply($value, $options = [])
    {
        return is_string($value) ? strtolower($value) : $value;
    }
}
