<?php namespace Naraki\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class BlogLabelRecord extends Model
{
    protected $table = 'blog_label_records';
    protected $primaryKey = 'blog_label_record_id';
    protected $fillable = [
        'blog_post_id',
        'blog_label_type_id'
    ];
    public $timestamps = false;
}