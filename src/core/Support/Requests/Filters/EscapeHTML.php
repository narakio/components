<?php namespace Naraki\Core\Support\Requests\Filters;

class EscapeHTML
{
    /**
     *  Remove HTML tags and encode special characters from the given string.
     *
     *  @param  string  $value
     *  @return string
     */
    public function apply($value, $options = [])
    {
        return is_string($value) ? filter_var($value, FILTER_SANITIZE_STRING) : $value;
    }
}
