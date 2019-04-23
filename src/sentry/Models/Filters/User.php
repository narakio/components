<?php namespace Naraki\Sentry\Models\Filters;

use Naraki\Core\Filters;
use Carbon\Carbon;

class User extends Filters
{
    protected $filters = ['sortBy', 'order', 'fullName', 'createdAt', 'group'];

    protected $acceptedSortColumns = ['full_name', 'email', 'created_at'];

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function sortBy($name)
    {
        $translatedColumn = trans(sprintf('nk::jsb.db_raw_inv.%s', $name));
        if (isset($this->acceptedSortColumns[$translatedColumn])) {
            return $this->builder
                ->orderBy($translatedColumn,
                    trans(
                        sprintf('nk::jsb.filters.%s',
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
    public function fullName($name)
    {
        return $this->builder->where(
            'full_name',
            'like',
            sprintf('%%%s%%', $name));
    }

    /**
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function group($name)
    {
        return $this->builder->groupMember($name);
    }

    /**
     * @param string $date
     * @return \Illuminate\Database\Query\Builder
     */
    public function createdAt($date)
    {
        switch ($date) {
            case trans('nk::jsb.filters.week'):
                $testedDate = Carbon::now()->subWeek()->toDateTimeString();
                break;
            case trans('nk::jsb.filters.month'):
                $testedDate = Carbon::now()->subMonth()->toDateTimeString();
                break;
            case trans('nk::jsb.filters.year'):
                $testedDate = Carbon::now()->subYear()->toDateTimeString();
                break;
            default:
                $testedDate = Carbon::now()->setTime(0, 0, 0)->toDateTimeString();
        }
        return $this->builder->where(
            'people.created_at',
            '>',
            $testedDate);
    }
}