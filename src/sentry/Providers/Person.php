<?php namespace Naraki\Sentry\Providers;

use Naraki\Core\EloquentProvider;
use Illuminate\Database\Eloquent\Builder;
use Naraki\Sentry\Contracts\Person as PersonInterface;

/**
 * @method \Naraki\Sentry\Models\Person createModel(array $attributes = [])
 * @method \Naraki\Sentry\Models\Person getOne($id, $columns = ['*'])
 */
class Person extends EloquentProvider implements PersonInterface
{
    protected $model = \Naraki\Sentry\Models\Person::class;

    public function buildOneBySlug($slug, $columns = ['*']): Builder
    {
        return $this
            ->select($columns)->where('person_slug', $slug);

    }

    /**
     * @param string $search
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($search, $limit): Builder
    {
        return $this
            ->select(['full_name as text', 'person_slug as id'])
            ->where('full_name', 'like', sprintf('%%%s%%', $search))
            ->limit($limit);

    }

}