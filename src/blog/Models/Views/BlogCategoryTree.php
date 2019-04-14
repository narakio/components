<?php namespace Naraki\Blog\Models\Views;

use Illuminate\Database\Eloquent\Model;

class BlogCategoryTree extends Model
{
    protected $table = 'blog_category_tree';
    protected $primaryKey = 'blog_category_id';

    //For documentation purposes
    protected $fillable = ['blog_category_id', 'label', 'lvl', 'id'];

}