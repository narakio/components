<?php namespace Naraki\Media\Facades;

use Naraki\Media\Contracts\Media as MediaContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Naraki\Media\Providers\Image image()
 * @method static \Naraki\Media\Providers\Text text()
 * @method static \Naraki\Media\Providers\File file()
 * @method static \Naraki\Media\Models\MediaEntity createModel()
 */
class Media extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MediaContract::class;
    }
}