<?php namespace Naraki\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class BlogLabelType extends Model
{
    protected $primaryKey = 'blog_label_type_id';
    protected $fillable = ['blog_label_id'];
    public $timestamps = false;

}