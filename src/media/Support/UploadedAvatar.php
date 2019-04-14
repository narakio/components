<?php namespace Naraki\Media\Support;

use Naraki\Media\Contracts\UploadedImage;
use Naraki\Core\Models\Entity;
use Naraki\Media\Models\Media;
use Illuminate\Support\Collection;

class UploadedAvatar extends ImageUpload implements UploadedImage
{

    /**
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $fileObject
     * @param string $targetName
     * @param $targetType
     * @param $mediaType
     * @throws \Exception
     */
    public function __construct($fileObject, $targetName, $targetType, $mediaType)
    {
        $this->filename = $fileObject->getClientOriginalName();
        $this->targetSlug = $targetName;
        $this->uuid = sprintf('%s_%s', trim(substr($targetName, 0, 31), '-'), makeHexUuid());
        $this->fileExtension = $fileObject->getClientOriginalExtension();
        $this->hddFilename = sprintf('%s.%s', $this->uuid, $this->fileExtension);
//        $this->hddPath = media_entity_root_path(Entity::getConstant($targetType), Media::getConstant($mediaType));
        $this->hddPath = sprintf('%s/media/tmp/', public_path());
        $this->fileObject = $fileObject;
        $this->targetType = Entity::getConstant($targetType);
        $this->mediaType = Media::getConstant($mediaType);
    }

    public function move()
    {
        $this->fileObject->move($this->hddPath, $this->hddFilename);
    }

    public function saveTemporaryAvatar()
    {
        $this->move();
        if (\Cache::has('temporary_avatars')) {
            $data = \Cache::get('temporary_avatars');
        } else {
            $data = new Collection();
        }
        $data->put($this->uuid,
            new SimpleUploadedImage(
                $this->filename,
                $this->targetSlug,
                $this->targetType,
                $this->mediaType,
                $this->fileExtension,
                $this->uuid
            )
        );
        \Cache::put('temporary_avatars', $data, 7200);
    }

    public function processImage()
    {
        //$image = Image::makeCroppedImage($this->fileObject->getRealPath(),MediaTypeImgFormat::THUMBNAIL);
        //Image::saveImg($image,$this->newFullPath);
    }


}