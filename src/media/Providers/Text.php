<?php namespace Naraki\Media\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Media\Contracts\Text as TextInterface;

/**
 * @method \Naraki\Media\Models\MediaTxt createModel(array $attributes = [])
 */
class Text extends EloquentProvider implements TextInterface
{

    /**
     * @var string This provider's model class
     */
//    protected $model = \Naraki\Media\Models\MediaTypeTxt::class;

    public function __construct($model = null)
    {
        parent::__construct($model);
    }
}