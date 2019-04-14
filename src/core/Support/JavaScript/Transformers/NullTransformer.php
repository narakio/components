<?php namespace Naraki\Core\Support\JavaScript\Transformers;

class NullTransformer
{
    /**
     * Transform "null."
     *
     * @param  mixed $value
     * @return string
     */
    public function transform($value)
    {
        if (is_null($value)) {
            return 'null';
        }
    }
}