<?php namespace Naraki\Forum\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Forum\Contracts\Forum as ForumInterface;
use Naraki\Forum\Contracts\Board as BoardInterface;
use Naraki\Forum\Contracts\Thread as ThreadInterface;
use Naraki\Forum\Contracts\Post as PostInterface;

class Forum extends EloquentProvider implements ForumInterface
{
    /**
     * @var \Naraki\Forum\Contracts\Board|\Naraki\Forum\Providers\Board
     */
    private $board;
    /**
     * @var \Naraki\Forum\Contracts\Thread|\Naraki\Forum\Providers\Thread
     */
    private $thread;
    /**
     * @var \Naraki\Forum\Contracts\Post|\Naraki\Forum\Providers\Post
     */
    private $post;

    /**
     *
     * @param \Naraki\Forum\Contracts\Board|\Naraki\Forum\Providers\Board $bi
     * @param \Naraki\Forum\Contracts\Thread |\Naraki\Forum\Providers\Thread $ti
     * @param \Naraki\Forum\Contracts\Post|\Naraki\Forum\Providers\Post $pi
     */
    public function __construct(BoardInterface $bi, ThreadInterface $ti, PostInterface $pi)
    {
        parent::__construct();
        $this->board = $bi;
        $this->thread = $ti;
        $this->post = $pi;
    }

    public function board()
    {
        return $this->board;
    }

    public function thread()
    {
        return $this->board();
    }

    public function post()
    {
        return $this->post;
    }


}