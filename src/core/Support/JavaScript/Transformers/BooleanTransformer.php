<?php namespace Naraki\Core\Support\JavaScript\Transformers;

class BooleanTransformer
{
    /**
     * Transform a boolean.
     *
     * @param  boolean $value
     * @return string
     */
    public function transform($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
    }
}