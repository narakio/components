<?php namespace Naraki\Core\Traits\Models;

use Naraki\Core\Filters;
use Illuminate\Database\Eloquent\Builder;

trait Presentable
{
    /**
     * View presenter instance
     *
     * @var mixed
     */
    protected $presenterInstance;

    /**
     * Prepare a new or cached presenter instance
     *
     * @param string $method
     *
     * @param array $params
     * @return mixed
     */
    public function present($method = null, $params = [])
    {
        if (!$this->presenterInstance) {
            $this->presenterInstance = new $this->presenter($this);
        }

        if (!is_null($method)) {
            return call_user_func([$this->presenterInstance, $method], ...$params);
        }

        return $this->presenterInstance;
    }

    /**
     * @param $columns
     * @param $filter \Naraki\Core\Filters
     * @return array
     */
    public function getColumnInfo($columns, $filter): array
    {
        $sortable = array_flip($this->sortable);
        $result = [];
        foreach ($columns as $name => $info) {
            $result[$name] = [
                'name' => $name,
                'width' => (isset($info->width)) ? $info->width : 'inherit',
                'label' => $info->name,
                'sortable' => isset($sortable[$name])
            ];
            if ($name === $filter->getFilter(trans('js-backend.filters.sortBy'))) {
                $result[$name]['order'] = $filter->getFilter(trans('js-backend.filters.order'));
            }
        }
        return $result;
    }

    /**
     * @param mixed $query
     * @param \Naraki\Core\Filters $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, ?Filters $filters): Builder
    {
        if (!is_null($filters)) {
            return $filters->apply($query);
        }
        return $query;
    }

}