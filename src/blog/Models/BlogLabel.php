<?php namespace Naraki\Blog\Models;

use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class BlogLabel extends Model
{
    use Enumerable;

    protected $primaryKey = 'blog_label_id';
    protected $fillable = ['blog_label_name'];
    public $timestamps = false;

    const BLOG_TAG = 1;
    const BLOG_CATEGORY = 2;
}