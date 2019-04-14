<?php namespace Naraki\Forum\Events;

use Naraki\Core\Event;
use Illuminate\Contracts\Auth\Authenticatable;
use Naraki\Forum\Requests\CreateComment;

class PostCreated extends Event
{
    /**
     * @var array
     */
    public $request;
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|\Naraki\Sentry\Models\User
     */
    public $user;
    /**
     * @var \stdClass
     */
    public $entityData;
    /**
     * @var string
     */
    public $commentData;

    /**
     *
     * @param $request
     * @param $user
     * @param $entityData
     * @param $commentData
     */
    public function __construct(
        CreateComment $request,
        Authenticatable $user,
        \stdClass $entityData,
        \stdClass $commentData
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->entityData = $entityData;
        $this->commentData = $commentData;
    }


}