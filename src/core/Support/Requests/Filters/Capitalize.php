<?php namespace Naraki\Core\Support\Requests\Filters;

class Capitalize
{
    /**
     *  Capitalize the given string.
     *
     *  @param  string  $value
     *  @return string
     */
    public function apply($value, $options = [])
    {
        return is_string($value) ? ucwords(strtolower($value)) : $value;
    }
}
