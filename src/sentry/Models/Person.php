<?php

namespace Naraki\Sentry\Models;

use Naraki\Core\Traits\Models\HasASlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Person extends Model
{
    use HasASlug;

    public $table = 'people';
    public $primaryKey = 'person_id';
    public static $slugColumn = 'person_slug';

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'full_name',
        'user_id',
        'person_slug',
        'created_at',
        'updated_at'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                $model->person_slug = slugify(
                    substr(sprintf('%s %s', $model->first_name, $model->last_name), 0, 150)
                );
                if (empty($model->person_slug)) {
                    $model->person_slug = slugify(
                        substr($model->full_name, 0, 150)
                    );
                }
                if (empty($model->person_slug)) {
                    $model->person_slug = Str::random(15);
                }

                $latestSlug =
                    static::select(['person_slug'])
                        ->whereRaw(sprintf(
                                'person_slug = "%s" or person_slug LIKE "%s-%%"',
                                $model->person_slug,
                                $model->person_slug)
                        )
                        ->latest($model->getKeyName())
                        ->value('person_slug');
                if ($latestSlug) {
                    $pieces = explode('-', $latestSlug);

                    $number = intval(end($pieces));

                    $model->person_slug .= sprintf('-%s', ($number + 1));
                }
            }
        );
    }

    /**
     * @param string $email
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function buildByEmail($email, $columns = ['*']): Builder
    {
        return Person::query()->select($columns)
            ->where('email', '=', $email);
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeBlogPost(Builder $builder)
    {
        return $builder->join('blog_posts',
            'blog_posts.person_id', '=', 'people.person_id'
        );
    }

}
