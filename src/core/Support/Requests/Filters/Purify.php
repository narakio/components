<?php namespace Naraki\Core\Support\Requests\Filters;

use Mews\Purifier\Facades\Purifier;

class Purify
{
    /**
     *  Strip tags from the given string.
     *
     * @param  string $value
     * @param array $options
     * @return string
     */
    public function apply($value, $options = [])
    {
        return Purifier::clean($value, $options);
    }
}
