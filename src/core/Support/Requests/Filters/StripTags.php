<?php namespace Naraki\Core\Support\Requests\Filters;

class StripTags
{
    /**
     *  Strip tags from the given string.
     *
     *  @param  string  $value
     *  @return string
     */
    public function apply($value, $options = [])
    {
        return is_string($value) ? strip_tags($value) : $value;
    }
}
