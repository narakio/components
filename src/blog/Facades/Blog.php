<?php namespace Naraki\Blog\Facades;

use Naraki\Blog\Contracts\Blog as BlogContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Naraki\Blog\Providers\Category category()
 * @method static \Naraki\Blog\Providers\Tag tag()
 * @method static \Naraki\Blog\Providers\Source source()
 * @method static \Naraki\Blog\Providers\Blog blog()
 */
class Blog extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return BlogContract::class;
    }
}