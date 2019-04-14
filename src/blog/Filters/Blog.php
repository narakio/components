<?php namespace Naraki\Blog\Filters;

use Naraki\Core\Filters;

class Blog extends Filters
{
    protected $filters = ['sortBy', 'order', 'title'];
    protected $acceptedSortColumns = ['blog_post_title'];

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function sortBy($name)
    {
        if (isset($this->acceptedSortColumns[$name])) {
            return $this->builder
                ->orderBy($name,
                    trans(
                        sprintf('js-backend.filters.%s',
                            $this->getFilter('order')
                        )
                    ) ?? 'asc'
                );
        }
        return $this->builder;
    }

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function title($name)
    {
        return $this->builder->where(
            'blog_post_title',
            'like',
            sprintf('%%%s%%', $name));
    }

}