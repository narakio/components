<?php namespace Naraki\Forum\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Naraki\Core\Controllers\Frontend\Controller;
use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Forum\Events\PostCreated;
use Naraki\Forum\Facades\Forum;
use Naraki\Forum\Requests\CreateComment;
use Naraki\Media\Models\Media;
use Naraki\Media\Models\MediaEntity;
use Naraki\Media\Models\MediaImgFormat;
use Naraki\System\Facades\System;

class Post extends Controller
{
    /**
     * @param string $type
     * @param string $slug
     * @param string $sort
     * @param string $order
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getComments($type, $slug, $sort = 'updated_at', $order = 'desc')
    {
        $entityTypeId = EntityType::getEntityTypeID(Entity::getConstant($type), $slug);
        $favorites = Session::get('favorite_list');
        $postCollection = Forum::post()->buildComments($entityTypeId)->get();
        $postUsers = $postCollection->pluck('username')->unique();
        $dbPosts = $postCollection->toArray();
        if (!empty($dbPosts)) {
            $userId = !is_null(auth()->user()) ? auth()->user()->getKey() : null;
            $userMedia = EntityType::getEntityInfoFromSlug(Entity::USERS, $postUsers->toArray())
                ->scopes(['avatars'])->select(['username', 'media_uuid as uuid', 'media_extension as ext'])->get();
            unset($postCollection, $postUsers);
            $media = [];
            foreach ($userMedia as $m) {
                $media[$m->getAttribute('username')] = $m;
            }
            foreach ($dbPosts as $key => $user) {
                $dbPosts[$key]['url'] = route_i18n('user', ['slug' => $dbPosts[$key]['username']]);
                $dbPosts[$key]['owns'] = $dbPosts[$key]['user_id'] == $userId;
                $dbPosts[$key]['fav'] = isset($favorites[$dbPosts[$key]['slug']]);
                $dbPosts[$key]['edit_mode'] = false;
                unset($dbPosts[$key]['user_id']);

                if (isset($media[$user['username']])) {
                    $dbPosts[$key]['media'] = MediaEntity::assetStatic(
                        Entity::USERS,
                        Media::IMAGE_AVATAR,
                        $media[$user['username']]['uuid'],
                        $media[$user['username']]['ext'], MediaImgFormat::ORIGINAL
                    );

                } else {
                    $dbPosts[$key]['media'] = null;
                }
            }
        }
        $posts = Forum::post()->getTree($dbPosts, $sort, $order);
        $notification_options = null;
        if (auth()->check()) {
            $notification_options = System::subscriptions()->cacheFrontendSubscriptions(auth()->user()->getKey());
            $events = System::getFrontendEvents();

            if (empty($notification_options)) {
                $notification_options = [];
                foreach ($events as $event) {
                    $notification_options[$event['id']] = false;
                }
            }
        }
        return response(compact('posts', 'notification_options', 'events'), 200);
    }

    /**
     * @param string $type
     * @param string $slug
     * @param \Naraki\Forum\Requests\CreateComment $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createComment($type, $slug, CreateComment $request)
    {
        $entityId = Entity::getConstant($type);
        $entity = EntityType::getEntityInfoFromSlug($entityId, $slug)
            ->select(['entity_type_id', 'blog_post_title as title', 'blog_post_slug as slug', 'person_id'])
            ->first();

        if (is_null($entity)) {
            return response(
                sprintf('%s %s not found.', $type, $slug),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $entityTypeId = $entity->getAttribute('entity_type_id');
        $comment = Forum::post()->createComment(
            (object)$request->all(), $entityTypeId, auth()->user()
        );
        Session::put('last_comment', Carbon::now());

        event(new PostCreated(
                $request,
                auth()->user(),
                (object)$entity->toArray(),
                (object)$comment->toArray()
            )
        );

        return response([
            'type' => 'success',
            'title' => (!$request->has('reply_to')
                ? trans('messages.comment_add_success')
                : trans('messages.reply_add_success')
            )
        ], Response::HTTP_OK);
    }

    /**
     * @param string $type
     * @param string $slug
     * @param \Naraki\Forum\Requests\CreateComment $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateComment($type, $slug, CreateComment $request)
    {
        Forum::post()->updateComment(
            (object)$request->all(), auth()->user()
        );
        Session::put('last_comment', Carbon::now());
        return response([
            'type' => 'success',
            'title' => trans('messages.comment_update_success')
        ], Response::HTTP_OK);

    }

    /**
     * @param string $type
     * @param string $slug
     * @param string $commentSlug
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function deleteComment($type, $slug, $commentSlug)
    {
        Forum::post()->deleteComment($commentSlug, auth()->user());
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $type
     * @param string $slug
     * @param string $commentSlug
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function favorite($type, $slug, $commentSlug)
    {
        $userFavoriteList = Session::get('favorite_list');
        if (is_null($userFavoriteList)) {
            $userFavoriteList = new Collection([$commentSlug => true]);
        } else {
            $userFavoriteList->put($commentSlug, true);
        }
        Session::put('favorite_list', $userFavoriteList);
        Forum::post()->favorite($commentSlug);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $type
     * @param string $slug
     * @param string $commentSlug
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function unfavorite($type, $slug, $commentSlug)
    {
        $userFavoriteList = Session::get('favorite_list');
        if (!is_null($userFavoriteList)) {
            $userFavoriteList->forget($commentSlug);
            Session::put('favorite_list', $userFavoriteList);
            Forum::post()->unfavorite($commentSlug);
        }
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**.
     * @param $type
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateNotifications($type)
    {

        System::subscriptions()->saveFrontend(auth()->user()->getKey(), app('request')->get('data'));

        return response(null, Response::HTTP_NO_CONTENT);
    }

}