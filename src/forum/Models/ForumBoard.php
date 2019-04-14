<?php namespace Naraki\Forum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ForumBoard extends Model
{
    protected $primaryKey = 'forum_board_id';
    protected $fillable = [
        'language_id',
        'forum_board_name',
        'forum_board_slug'
    ];
    public $timestamps = false;

}
