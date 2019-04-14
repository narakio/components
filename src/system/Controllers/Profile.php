<?php namespace Naraki\System\Controllers;

use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Sentry\Requests\Admin\UpdateUser;
use Naraki\Sentry\Jobs\UpdateUserElasticsearch;
use Naraki\Core\Models\Entity;
use Naraki\Sentry\Providers\User as UserProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Naraki\Media\Facades\Media as MediaProvider;

class Profile extends Controller
{
    use DispatchesJobs;
    /**
     * Update the user's profile information.
     *
     * @param \Naraki\Sentry\Requests\Admin\UpdateUser $request
     * @param \Naraki\Sentry\Providers\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUser $request, UserProvider $user)
    {
        $savedUser = $user->updateOneByUsername(
            $this->user->getAttribute('username'),
            $request->all()
        );

        return response([
            'user' => $savedUser->only(['username', 'first_name', 'last_name', 'email']),
        ],Response::HTTP_OK);
    }

    /**
     * @param \Naraki\Sentry\Providers\User $user
     * @return \Illuminate\Http\Response
     */
    public function avatar(UserProvider $user)
    {
        return response($this->getAvatars($user), Response::HTTP_OK);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Naraki\Sentry\Providers\User $user
     * @return \Illuminate\Http\Response
     */
    public function setAvatar(Request $request, UserProvider $user)
    {
        MediaProvider::image()->setAsUsed(
            $request->get('uuid'),
            intval(auth()->user()->getAttribute('entity_type_id'))
        );
        $this->dispatch(new UpdateUserElasticsearch(
                UpdateUserElasticsearch::WRITE_MODE_UPDATE,
                auth()->user()->getKey())
        );
        return response($this->getAvatars($user), Response::HTTP_OK);
    }

    /**
     * @param int $uuid
     * @param \Naraki\Sentry\Providers\User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function deleteAvatar($uuid, UserProvider $user)
    {
        MediaProvider::image()->delete(
            $uuid,
            Entity::USERS,
            \Naraki\Media\Models\Media::IMAGE_AVATAR
        );

        return response($this->getAvatars($user), Response::HTTP_OK);
    }

    /**
     * @param \Naraki\Sentry\Providers\User $user
     * @return mixed
     */
    private function getAvatars(UserProvider $user)
    {
        return $user->getAvatars($this->user->getKey());
    }

}
