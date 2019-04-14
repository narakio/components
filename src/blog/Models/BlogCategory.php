<?php namespace Naraki\Blog\Models;

use Naraki\Core\Support\NestedSet\NodeTrait;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class BlogCategory extends Model
{
    use NodeTrait, DoesSqlStuff;

    protected $table = 'blog_categories';
    protected $primaryKey = 'blog_category_id';
    protected $fillable = [
        'blog_category_name',
        'blog_label_type_id',
        'blog_category_slug'
    ];
    protected $hidden = ['parent_id', 'lft', 'rgt'];

    public static function boot()
    {
        parent::boot();

        static::creating(function($model){
            forward_static_call([static::class,'checkSlug'],$model);
        });

        static::updating(function($model){
            forward_static_call([static::class,'checkSlug'],$model);
        });
    }

    private static function checkSlug($model)
    {
        $col = 'blog_category_slug';
        $model->{$col} = slugify(
            substr($model->blog_category_name, 0, 75),
            '-',
            app()->getLocale()
        );

        $latestSlug = static::select([$col])
            ->whereRaw(sprintf('%1$s = "%2$s" or %1$s LIKE "%2$s-%%"', $col, $model->{$col}))
            ->latest($model->getKeyName())
            ->pluck($col)->first();
        if (!is_null($latestSlug)) {
            $pieces = explode('-', $latestSlug);
            $number = intval(end($pieces));
            $model->{$col} .= sprintf('-%s', ($number + 1));
        }
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeLabelType(Builder $builder)
    {
        return $this->joinReverse($builder, BlogLabelType::class);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $blogPostId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeLabelRecord(Builder $builder, $blogPostId = null)
    {
        return $builder->join('blog_label_records', function (JoinClause $q) use ($blogPostId) {
            $q->on(
                'blog_label_types.blog_label_type_id',
                '=',
                'blog_label_records.blog_label_type_id'
            );
            if (!is_null($blogPostId)) {
                $q->where('blog_post_id', '=', $blogPostId);
            }
        });
    }
}