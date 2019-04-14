<?php namespace Naraki\Media\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Media\Contracts\File as FileInterface;
use Naraki\Media\Contracts\Image as ImageInterface;
use Naraki\Media\Contracts\Text as TextInterface;

/**
 * @method \Naraki\Media\Models\MediaType createModel(array $attributes = [])
 */
class File extends EloquentProvider implements FileInterface
{
    /**
     * @var string This provider's model class
     */
    protected $model = \Naraki\Media\Models\MediaDigital::class;

    /**
     * @var \Naraki\Media\Contracts\Image|\Naraki\Media\Providers\Image
     */
    protected $image;

    /**
     * @var \Naraki\Media\Contracts\Text|\Naraki\Media\Providers\Text
     */
    protected $text;

    /**
     * File constructor.
     *
     * @param \Naraki\Media\Contracts\Image|\Naraki\Media\Providers\Image $i
     * @param \Naraki\Media\Contracts\Text|\Naraki\Media\Providers\Text $t
     * @param null $model
     */
    public function __construct(ImageInterface $i, TextInterface $t, $model = null)
    {
        parent::__construct($model);
        $this->image = $i;
        $this->text  = $t;
    }

    /**
     * @return \Naraki\Media\Contracts\Image|\Naraki\Media\Providers\Image
     */
    public function image()
    {
        return $this->image;
    }

    /**
     * @return \Naraki\Media\Contracts\Text|\Naraki\Media\Providers\Text
     */
    public function text()
    {
        return $this->text;
    }

}