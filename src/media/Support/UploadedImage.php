<?php namespace Naraki\Media\Support;

use Naraki\Media\Contracts\UploadedImage as UploadedImageContract;
use Naraki\Core\Models\Entity;
use Naraki\Media\Models\Media;

class UploadedImage extends ImageUpload implements UploadedImageContract
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
        $this->uuid = sprintf('%s_%s', trim(substr($targetName, 0, 31),'-'), makeHexUuid());
        $this->fileExtension = $fileObject->getClientOriginalExtension();
        $this->hddFilename = sprintf('%s.%s', $this->uuid, $this->fileExtension);
        $this->hddPath = media_entity_root_path($targetType, $mediaType);
        $this->targetType = Entity::getConstant($targetType);
        $this->mediaType = Media::getConstant($mediaType);
        $this->fileObject = $fileObject;
        $this->thumbnailFilename = ImageProcessor::makeFormatFilenameFromImageFilename(
            $this->hddFilename
        );
    }

    public function move()
    {
        $this->fileObject->move($this->hddPath, $this->hddFilename);
    }

    public function makeThumbnail()
    {
        $imageFullPath = $this->hddPath . $this->hddFilename;
        ImageProcessor::saveImg(
            ImageProcessor::makeCroppedImage($imageFullPath),
            media_entity_root_path(
                $this->targetType,
                $this->mediaType,
                $this->thumbnailFilename
            )
        );
    }

    public function processImage()
    {
        //$image = Image::makeCroppedImage($this->fileObject->getRealPath(),MediaTypeImgFormat::THUMBNAIL);
        //Image::saveImg($image,$this->newFullPath);
    }

}