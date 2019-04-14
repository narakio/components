<?php namespace Naraki\Forum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class ForumThread extends Model
{
    protected $primaryKey = 'forum_thread_id';

    protected $fillable = [
        'forum_board_id',
        'forum_thread_first_post_id',
        'thread_user_id',
        'forum_thread_title',
        'forum_thread_last_post'
    ];

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $boardID
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeBoard(Builder $query, $boardID = null)
    {
        return $query->join('forum_boards', function (JoinClause $q) use ($boardID) {
            $q->on('forum_boards.forum_board_id', '=', 'forum_threads.forum_board_id');
            if (!is_null($boardID)) {
                $q->where('forum_boards.forum_board_id', '=', $boardID);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUser(Builder $query)
    {
        return $query->join('users',
            'users.user_id', '=', 'forum_threads.thread_user_id');
    }

}
