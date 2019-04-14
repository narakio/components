<?php namespace Naraki\Forum\Models;

use Naraki\Core\Models\Entity;
use Naraki\Core\Support\NestedSet\NodeTrait;
use Naraki\Core\Traits\Models\HasASlug;
use Naraki\Core\Traits\Models\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class ForumPost extends Model
{
    use HasASlug, Presentable, NodeTrait;

    protected $primaryKey = 'forum_post_id';
    protected $presenter = null;

    protected $fillable = [
        'entity_type_id',
        'post_user_id',
        'forum_post',
        'forum_post_slug',
        'forum_post_fav_cnt as cnt'
    ];
    public static $slugColumn = 'forum_post_slug';

//    public function getUpdatedAtAttribute($value)
//    {
//        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
//    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $threadID
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeThread(Builder $query, $threadID = null)
    {
        return $query->join('forum_threads', function (JoinClause $q) use ($threadID) {
            $q->on('forum_threads.forum_thread_id', '=', 'forum_posts.forum_thread_id');
            if (!is_null($threadID)) {
                $q->where('forum_threads.forum_thread_id', $threadID);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param int $boardID
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
     * @param int $entityTypeId
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEntityType(Builder $query, $entityId = Entity::BLOG_POSTS, $entityTypeId = null)
    {
        return $query->join('entity_types', function (JoinClause $q) use ($entityTypeId, $entityId) {
            $q->on('entity_types.entity_type_id', '=', 'entity_types.entity_type_id');
            $q->where('entity_id', $entityId);
            if (!is_null($entityTypeId)) {
                $q->where('entity_types.entity_type_id', '=', $entityTypeId);
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
            'users.user_id', '=', 'forum_posts.post_user_id')
            ->join('people', 'people.user_id', '=', 'users.user_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeTree(Builder $query)
    {
        return $query->join('forum_post_tree',
            'forum_post_tree.id', '=', 'forum_posts.forum_post_id');
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getHierarchy($slug)
    {
        return \DB::select('
            select bct.label,bct.id,bct.lvl from blog_category_tree, blog_category_tree as bct
            where blog_category_tree.id=?
            and blog_category_tree.lft between bct.lft and bct.rgt
        ', [$slug]);

    }


}
