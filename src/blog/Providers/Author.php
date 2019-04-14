<?php namespace Naraki\Blog\Providers;

use Naraki\Blog\Contracts\Author as AuthorInterface;
use Naraki\Sentry\Models\Person;
use Naraki\Core\EloquentProvider;

class Author extends EloquentProvider implements AuthorInterface
{
    protected $model = Person::class;

    /**
     * Counts all occurrences in a table
     *
     * @return int
     */
    public function countAll()
    {
        $model = $this->createModel();

        $result = \DB::select(sprintf('
            select count(%1$s) as c from people where %1$s in (
              select distinct %1$s
              from blog_posts
            )', $model->getKeyName()));
        return !empty($result) ? $result[0]->c : null;
    }

    /**
     * @param int $n
     * @param string  $columns
     * @return string
     */
    public function getNth($n, $columns)
    {
        return $this->select([$columns])
            ->scopes(['blogPost'])
            ->groupBy('people.person_id', 'updated_at')
            ->orderBy($columns, 'desc')
            ->limit(1)
            ->offset($n)->value('updated_at');
    }

    /**
     * @param int $n
     * @param int $offset
     * @param array $columns
     * @param string $order
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getN($n, $offset, $columns, $order, $direction = 'desc')
    {
        return $this->select($columns)
            ->scopes(['blogPost'])
            ->groupBy(...$columns)
            ->orderBy($order, $direction)
            ->limit($n)
            ->offset($offset);
    }

}