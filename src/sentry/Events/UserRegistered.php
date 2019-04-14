<?php namespace Naraki\Sentry\Events;

use Naraki\Core\Event;
use Naraki\Sentry\Models\User;

class UserRegistered extends Event
{
    /**
     * @var string
     */
    private $token;
    /**
     * @var \Naraki\Sentry\Models\User
     */
    private $user;

    /**
     * @param \Naraki\Sentry\Models\User $user
     * @param string $token
     */
    public function __construct(User $user, $token = null)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @return \Naraki\Sentry\Models\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function hasToken()
    {
        return !is_null($this->token);
    }
}