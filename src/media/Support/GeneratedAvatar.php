<?php namespace Naraki\Media\Support;

use Naraki\Media\Contracts\UploadedImage;
use Laravolt\Avatar\Avatar;

class GeneratedAvatar extends ImageUpload implements UploadedImage
{
    protected $fileExtension = 'png';
    /**
     * @var \Laravolt\Avatar\Avatar
     */
    private $avatar;

    /**
     *
     * @param $targetSlug
     * @param $targetName
     * @param $targetType
     * @param $mediaType
     */
    public function __construct($targetSlug, $targetName, $targetType, $mediaType)
    {
        $this->targetName = $targetName;
        $this->targetSlug = $targetSlug;
        $this->uuid = sprintf(
            '%s_%s',
            substr(trim(slugify($targetName), '-'), 0, 31),
            makeHexUuid()
        );
        $this->filename = sprintf('%s.%s', $this->uuid, $this->fileExtension);
        $this->path = media_entity_root_path($targetType, $mediaType);
        $this->targetType = $targetType;
        $this->mediaType = $mediaType;
        $this->avatar = new Avatar(app('config')->get('laravolt.avatar'));
    }

    /**
     * @return \Naraki\Media\Support\GeneratedAvatar
     */
    public function processAvatar(): self
    {
        $this->avatar
            ->create(strtoupper($this->targetName))
            ->save($this->path . $this->filename);
        return $this;
    }


}