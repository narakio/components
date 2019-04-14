<?php namespace Naraki\Blog\Models;

use Naraki\Core\Traits\Models\DoesSqlStuff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Naraki\Elasticsearch\Contracts\Searchable as SearchableContract;
use Naraki\Elasticsearch\Searchable;

class BlogTag extends Model implements SearchableContract
{
    use DoesSqlStuff, Searchable;

    protected $table = 'blog_tags';
    protected $primaryKey = 'blog_tag_id';
    protected $fillable = ['blog_tag_name', 'blog_tag_slug','blog_label_type_id'];
    public $timestamps = false;

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
    public function scopeLabelRecord(Builder $builder, $blogPostId = null)
    {
        return BlogCategory::scopeLabelRecord($builder, $blogPostId);
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeBlogPost(Builder $builder)
    {
        return $builder->join('blog_posts',
            'blog_label_records.blog_post_id', '=', 'blog_posts.blog_post_id'
        );
    }
}