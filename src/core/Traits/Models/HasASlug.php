<?php namespace Naraki\Core\Traits\Models;

trait HasASlug
{
    /**
     * Get the name of the column in the entity's table that gives it its name, i.e name, title, label, etc.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->getAttribute(static::$slugColumn);
    }

    /**
     * @return string
     */
    public function getSlugName()
    {
        return static::$slugColumn;
    }

    /**
     * Get the field that contains a presentable identifier for the resource
     * i.e a blog post title, a person's full name as opposed to their id or slug
     *
     * @return string
     */
    public function getPretty()
    {
        return $this->getAttribute(static::$prettyColumn);
    }

    /**
     * @return string
     */
    public function getPrettyName()
    {
        return static::$prettyColumn;
    }

}
