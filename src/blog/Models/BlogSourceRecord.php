<?php namespace Naraki\Blog\Models;

use Naraki\Core\Traits\Models\DoesSqlStuff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlogSourceRecord extends Model
{
    use DoesSqlStuff;

    public $timestamps = false;
    protected $primaryKey = 'blog_source_record_id';
    protected $fillable = [
        'blog_post_id',
        'blog_source_id',
        'blog_source_content'
    ];

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePost(Builder $builder, $slug = null): Builder
    {
        return $this->joinReverseWithSlug($builder, BlogPost::class, $slug);
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeSource(Builder $builder){
        return $this->joinWithKey($builder,BlogSource::class,'blog_source_id');
    }

}