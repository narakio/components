<?php namespace Naraki\Media\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Media\Contracts\Avatar as AvatarInterface;

/**
 * @method \Naraki\Media\Models\MediaType createModel(array $attributes = [])
 */
class Avatar extends EloquentProvider implements AvatarInterface
{
    protected $model = \Naraki\Media\Models\MediaType::class;



}