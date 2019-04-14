<?php namespace Naraki\Forum\Models\Views;

use Illuminate\Database\Eloquent\Model;

class ForumPostTree extends Model
{
    protected $table = 'forum_post_tree';
    protected $primaryKey = 'id';

    //For documentation purposes
    protected $fillable = ['id', 'lvl'];

}