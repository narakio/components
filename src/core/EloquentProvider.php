<?php namespace Naraki\Core;

use Illuminate\Database\Eloquent\Builder;

abstract class EloquentProvider
{
    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * ModelProvider constructor.
     *
     * @param string $model
     */
    public function __construct($model = null)
    {
        if (!empty($model)) {
            $this->model = $model;
        }
    }

    /**
     * Create a new instance of the model.
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @see Model::__construct
     *
     */
    public function createModel(array $attributes = [])
    {
        $class = '\\' . ltrim($this->model, '\\');

        if (class_exists($class)) {
            return new $class($attributes);
        }
        throw new \UnexpectedValueException(
            'Model class could not be found or class variable has not been initialized.'
        );
    }

    /**
     * Gets the name of the Eloquent model.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->model;
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($columns = ['*'])
    {
        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getKeyName(), '>', 1)
            ->get($columns);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildOne($id)
    {
        $model = $this->createModel();

        return $model->newQuery()->where(
            sprintf('%s.%s',
                $model->getTable(),
                $model->getKeyName()
            ), $id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function noScopes()
    {
        return $this->createModel()->newQueryWithoutScopes();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function build()
    {
        return $this->createModel()->newQuery();
    }

    /**
     * @param array $columns
     * @param array $scopes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildWithScopes(array $columns, array $scopes): Builder
    {
        return $this->select($columns)->scopes($scopes);
    }

    /**
     * @param array $columns
     * @param array $scopes
     * @param array $wheres
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildOneWithScopes(array $columns, array $scopes, array $wheres): Builder
    {
        $q = $this->buildWithScopes($columns, $scopes);
        foreach ($wheres as $where) {
            if (!is_array($where)) {
                throw new \UnexpectedValueException('Where clause must be enclosed in an array');
            }
            $operator = '=';
            if (count($where) === 3) {
                list($column, $operator, $value) = $where;
            } else {
                list($column, $value) = $where;
            }
            if (!is_array($value)) {
                $q->where($column, $operator, $value);
            } else {
                $q->whereIn($column, $value);
            }
        }
        return $q;
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function select($columns = ['*'])
    {
        return $this->createModel()->newQuery()->select($columns);
    }

    /**
     * Counts all occurrences in a table
     *
     * @return int
     */
    public function countAll()
    {
        $model = $this->createModel();

        return intval($model->newQuery()
            ->select(
                \DB::raw(sprintf('count(%s) as cnt', $model->getKeyName())))
            ->value('cnt'));
    }

    /**
     * Filter the data array only keeping items matching the model's
     * fillable property
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return array
     */
    public function filterFillables(array $data, $model = null): array
    {
        if (is_null($model)) {
            $model = $this->createModel();
        }
        $fillables = array_flip($model->getFillable());

        return array_filter($data, function ($key) use ($fillables) {
            return isset($fillables[$key]);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param string $model
     * @return int|null
     */
    public static function getRowCount(string $model): ?int
    {
        /** @var \Illuminate\Database\Eloquent\Model $m */
        $m = new $model;
        $result = \DB::select(
            sprintf('SELECT count(%s) AS c FROM %s', $m->getKeyName(), $m->getTable()));
        return !empty($result) ? $result[0]->c : null;
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->createModel()->getKeyName();
    }

    /**
     * @return string
     */
    public function getQualifiedKeyName(): string
    {
        return $this->createModel()->getQualifiedKeyName();
    }

    /**
     * Stores the filters the user applied on a particular back office list,
     * i.e. sorting users by name.
     * The stored filters are used to build navigation arrows
     * to move between records without having to go back to the list.
     *
     * @param int $entityID
     * @param $userID
     * @param \Naraki\Core\Filters $filter
     * @return void
     */
    public function setStoredFilter(int $entityID, int $userID, Filters $filter)
    {
        if ($filter->hasFilters()) {
            \Cache::put($this->getStoredFilterKey($entityID, $userID), $filter, 60 * 60);
        } else {
            //If there are no filters, we make sure to erase any filters we kept that are no longer up to date
            \Cache::forget($this->getStoredFilterKey($entityID, $userID));
        }
    }

    /**
     * @param int $entityID
     * @param int $userID
     * @return void
     */
    public function resetStoredFilter(int $entityID, int $userID)
    {
        \Cache::forget($this->getStoredFilterKey($entityID, $userID));
    }

    /**
     * @param int $entityID
     * @param int $userID
     * @return \Naraki\Core\Filters|\null
     */
    public function getStoredFilter(int $entityID, int $userID): ?Filters
    {
        $filterID = $this->getStoredFilterKey($entityID, $userID);
        if (\Cache::has($filterID)) {
            return \Cache::get($filterID);
        }
        return null;
    }

    /**
     * @param $entityID
     * @param int $userID
     * @return string
     */
    private function getStoredFilterKey(int $entityID, int $userID): string
    {
        return sprintf('filter_%s_%s', $entityID, $userID);
    }

}