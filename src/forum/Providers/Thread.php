<?php namespace Naraki\Forum\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Forum\Contracts\thread as ThreadInterface;

class Thread extends EloquentProvider implements ThreadInterface
{
    protected $model = \Naraki\Forum\Models\ForumThread::class;

}