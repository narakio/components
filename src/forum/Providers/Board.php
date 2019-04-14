<?php namespace Naraki\Forum\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Forum\Contracts\Board as BoardInterface;

class Board extends EloquentProvider implements BoardInterface
{
    protected $model = \Naraki\Forum\Models\ForumBoard::class;


}