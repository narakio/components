<?php namespace Naraki\Core\Support\Requests\Filters;

class Uppercase
{
    /**
     *  Lowercase the given string.
     *
     *  @param  string  $value
     *  @return string
     */
    public function apply($value, $options = [])
    {
        return is_string($value) ? strtoupper($value) : $value;
    }
}
