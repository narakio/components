<?php namespace Naraki\Blog\Models;

use Naraki\Core\Contracts\Enumerable;
use Naraki\Core\Traits\Enumerable as EnumerableTrait;
use Illuminate\Database\Eloquent\Model;

class BlogStatus extends Model implements Enumerable
{
    use EnumerableTrait;

    public $timestamps = false;
    protected $table = 'blog_status';
    protected $primaryKey = 'blog_status_id';
    protected $fillable = ['blog_status_name'];

    const BLOG_STATUS_DRAFT = 1;
    const BLOG_STATUS_REVIEW = 2;
    const BLOG_STATUS_PUBLISHED = 3;

}