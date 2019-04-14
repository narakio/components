<?php namespace Naraki\Media\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Media\Contracts\File as FileInterface;
use Naraki\Media\Contracts\Media as MediaInterface;

/**
 * @method \Naraki\Media\Models\MediaEntity createModel(array $attributes = [])
 */
class Media extends EloquentProvider implements MediaInterface
{
    /**
     * @var \Naraki\Media\Contracts\File|\Naraki\Media\Providers\File
     */
    protected $file;

    /**
     * @var string This provider's model class
     */
    protected $model = \Naraki\Media\Models\MediaEntity::class;

    /**
     * Media constructor.
     *
     * @param \Naraki\Media\Contracts\File|\Naraki\Media\Providers\File $fi
     * @param string|null $model
     */
    public function __construct(FileInterface $fi, $model = null)
    {
        parent::__construct($model);
        $this->file = $fi;
    }

    /**
     * @return \Naraki\Media\Contracts\File|\Naraki\Media\Providers\File
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * @return \Naraki\Media\Contracts\Image|\Naraki\Media\Providers\Image
     */
    public function image()
    {
        return $this->file->image();
    }

    /**
     * @return \Naraki\Media\Contracts\Text
     */
    public function text()
    {
        return $this->file->text();
    }

    /**
     * @param int $mediaId
     *
     * @return string
     */
    public static function getSupportedFileFormats($mediaId)
    {
        switch ($mediaId) {
            default:
                return 'JPG, PNG';
                break;
        }
    }

    /**
     * @param array $columns
     * @param int $entityTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMedia($columns=['*'], $entityTypeId = null)
    {
        $model = $this->build()->select($columns);
        $scopes = [
            '',
            'mediaCategoryRecord',
            'mediaRecord',
            'mediaType',
            'mediaDigital',
            'media'
        ];
        if (!is_array($entityTypeId)) {
            $scopes[0] = 'entityType';
            return $model->scopes($scopes)->applyScopes();
        }
        array_shift($scopes);
        return $model->entityType($entityTypeId)->scopes($scopes);
    }

}