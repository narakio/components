<?php namespace Naraki\Forum\Facades;

use Illuminate\Support\Facades\Facade;
use Naraki\Forum\Contracts\Forum as ForumContract;

/**
 * @method static \Naraki\Forum\Providers\Board board()
 * @method static \Naraki\Forum\Providers\Thread thread()
 * @method static \Naraki\Forum\Providers\Post post()
 */
class Forum extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return ForumContract::class;
    }

}