<?php namespace Naraki\Mail\Support;

use Illuminate\Support\Collection;

class ViewData extends Collection
{
    /**
     * @param mixed $values
     * @return void
     */
    public function add($values)
    {
        foreach ($values as $k => $v) {
            $this->put($k, $v);
        }
    }
}