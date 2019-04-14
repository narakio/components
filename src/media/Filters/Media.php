<?php namespace Naraki\Media\Filters;

use Naraki\Core\Filters;

class Media extends Filters
{
    protected $filters = ['sortBy', 'order', 'title'];
    protected $acceptedSortColumns = ['media_title', 'created_at'];

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
            'media_title',
            'like',
            sprintf('%%%s%%', $name));
    }

}