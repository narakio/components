<?php namespace Naraki\Forum\Providers;

use Naraki\Core\EloquentProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Naraki\Forum\Contracts\Post as PostInterface;
use Naraki\Forum\Support\Trees\Post as PostTree;

/**
 * @method \Naraki\Forum\Models\ForumPost createModel(array $attributes = [])
 * @method \Naraki\Forum\Models\ForumPost getOne($id, $columns = ['*'])
 */
class Post extends EloquentProvider implements PostInterface
{
    protected $model = \Naraki\Forum\Models\ForumPost::class;

    /**
     * @param int $entityTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildComments(int $entityTypeId): Builder
    {
        //This query has to be sorted by the parent't left node, otherwise the tree won't render properly
        //Nodes have to be ordered from root to lowest children for every branch.
        //The ordering of the tree is done recursively via php.
        return $this->buildWithScopes(
            [
                'forum_posts.forum_post_id as id',
                'lvl',
                'forum_post_slug as slug',
                'forum_post as txt',
                'username',
                'post_user_id as user_id',
                'full_name as name',
                'forum_posts.updated_at',
                'forum_post_fav_cnt as cnt'
            ],
            ['entityType', 'user', 'tree'])
            ->where('entity_types.entity_type_id', $entityTypeId)
            ->orderBy('forum_post_tree.lft', 'asc');
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getUserPostTreeBySlug($slug)
    {
        return \DB::select('select users.user_id,username,full_name,email
from forum_post_tree, forum_post_tree as fp
join forum_posts on fp.id = forum_posts.forum_post_id
join users on forum_posts.post_user_id = users.user_id
join people on users.user_id = people.user_id
where forum_post_tree.lft between fp.lft and fp.rgt
and forum_post_tree.slug = ?', [$slug]);
    }

    /**
     * @param \stdClass $commentData
     * @param int $entityTypeId
     * @param \Illuminate\Contracts\Auth\Authenticatable|\Naraki\Sentry\Models\User $user
     * @return \Naraki\Forum\Models\ForumPost
     */
    public function createComment(\stdClass $commentData, int $entityTypeId, Authenticatable $user)
    {
        try {
            $parentPost = null;
            if (isset($commentData->reply_to) && !is_null($commentData->reply_to)) {
                $model = $this->createModel();
                /**
                 * @var \Naraki\Forum\Models\ForumPost $parentPost
                 */
                $parentPost = $model->newQuery()
                    ->select([$model->getKeyName(), $model->getRgtName(), $model->getLftName()])
                    ->where($model->getSlugName(), $commentData->reply_to)
                    ->first();
            }
            /**
             * @var \Naraki\Forum\Models\ForumPost $post
             */
            $post = $this->build()->create([
                'entity_type_id' => $entityTypeId,
                'post_user_id' => $user->getKey(),
                'forum_post' => $commentData->txt,
                'forum_post_slug' => sprintf('%s_%s',
                    mb_substr($user->getAttribute('person_slug'), 0, 31),
                    makeHexUuid()
                )
            ]);

            if (is_null($parentPost)) {
                $post->save();
            } else {
                $post->appendToNode($parentPost)->save();
            }
        } catch (\Exception $e) {
        }
        return $post;
    }

    /**
     * @param \stdClass $data
     * @param \Illuminate\Contracts\Auth\Authenticatable|\Naraki\Sentry\Models\User $user
     * @return void
     */
    public function updateComment($data, Authenticatable $user)
    {
        if (!isset($data->reply_to)) {
            return;
        }
        $model = $this->createModel();
        $q = $model->newQuery()->scopes(['entityType', 'user'])
            ->select([
                'post_user_id',
                $model->getKeyName(),
                $model->getParentIdName(),
                $model->getLftName(),
                $model->getRgtName()
            ])
            ->where($model->getSlugName(), $data->reply_to)->first();
        if (!is_null($q)) {
            if ($user->getKey() == $q->getAttribute('post_user_id')) {
                $q->update(['forum_post' => $data->txt]);
            }
        }

    }

    /**
     * @param array $posts
     * @param string $sortColumn
     * @param string $order
     * @return array
     */
    public function getTree(array $posts, $sortColumn = 'updated_at', $order = 'desc'): array
    {
        return PostTree::getTree(
            $posts,
            $sortColumn,
            $order);
    }

    /**
     * @param string $slug
     * @param Authenticatable|\Naraki\Sentry\Models\User $user
     * @return int
     * @throws \Exception
     */
    public function deleteComment(string $slug, Authenticatable $user)
    {
        $model = $this->createModel();
        $q = $model->newQuery()->scopes(['entityType', 'user'])
            ->select([
                'post_user_id',
                $model->getKeyName(),
                $model->getParentIdName(),
                $model->getLftName(),
                $model->getRgtName()
            ])
            ->where($model->getSlugName(), $slug)->first();
        if (!is_null($q)) {
            if ($user->getKey() == $q->getAttribute('post_user_id')) {
                return $q->delete();
            }
        }
        return 0;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function favorite(string $slug)
    {
        return \DB::unprepared(sprintf('CALL sp_increment_forum_post_favorite_count("%s")', $slug));
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function unfavorite(string $slug)
    {
        return \DB::unprepared(sprintf('CALL sp_decrement_forum_post_favorite_count("%s")', $slug));
    }

}