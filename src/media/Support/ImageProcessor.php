<?php namespace Naraki\Media\Support;

use Naraki\Media\Models\MediaImgFormat;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManagerStatic;

class ImageProcessor extends InterventionImage
{
    public static $thumbnailHeight = 256;
    public static $thumbnailWidth = 256;
    public static $imageDriver = 'imagick';
    public static $quality = 80;


    /**
     * @param string|\Psr\Http\Message\StreamInterface $path
     * @return \Intervention\Image\Image
     */
    public static function makeImg($path)
    {
        ImageManagerStatic::configure(array('driver' => static::$imageDriver));
        return ImageManagerStatic::make($path);
    }

    /**
     *
     * @param $path
     * @param int $formatId
     * @return \Intervention\Image\Image|string The resulting file's name or the image object
     */
    public static function makeCroppedImage($path, $formatId = MediaImgFormat::THUMBNAIL)
    {
        return static::resizeToFormat(static::makeImg($path), $formatId);
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param int $formatId
     *
     * @return \Intervention\Image\Image|string
     */
    private static function resizeToFormat(
        $image,
        $formatId = MediaImgFormat::THUMBNAIL
    ) {
        $format = MediaImgFormat::getFormatDimensions($formatId);

        if ($image->getHeight() > $format->height || $image->getWidth() > $format->width) {
            $image->fit($format->width, $format->height, function ($constraint) {
                /**
                 * @var \Intervention\Image\Constraint $constraint
                 */
                //Maintain aspect ratio
                $constraint->aspectRatio();
                //Prevent upsizing
                $constraint->upsize();
            });
        }
        return $image;
    }

    /**
     * Crops images
     *
     * @param string $path
     * @param \stdClass $imageAlterations
     * @param int $formatID
     *
     * @return \Intervention\Image\Image|boolean
     */
    public static function nipNTuck(
        $path,
        \stdClass $imageAlterations,
        $formatID = MediaImgFormat::THUMBNAIL
    ) {
        if ($formatID !== MediaImgFormat::ORIGINAL) {
            return static::resizeToFormat(static::adjustImage($path, $imageAlterations, false),
                $formatID);
        } else {
            //Saving the original
            $image = static::adjustImage($path, $imageAlterations);

            //Saving the thumbnail
            return static::resizeToFormat($image, MediaImgFormat::THUMBNAIL);
        }
    }

    /**
     * @param string $path
     * @param \stdClass $imageAlterations
     * @param boolean $save
     *
     * @return \Intervention\Image\Image
     */
    public static function adjustImage($path, \stdClass $imageAlterations, $save = true)
    {
        $image = static::makeImg($path);
        $imageChanged = false;

        //If height or width is different from original
        if ($image->getHeight() > $imageAlterations->height || $image->getWidth() > $imageAlterations->width) {
            $image->crop(ceil($imageAlterations->width), ceil($imageAlterations->height), ceil($imageAlterations->x),
                ceil($imageAlterations->y));
            $imageChanged = true;
        }

        if ($imageChanged === true && $save === true) {
            $image->save($path, static::$quality);
        }

        return $image;
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param int|null $fullPath
     * @return \Intervention\Image\Image|void
     */
    public static function saveImg($image, $fullPath)
    {
        $image->save($fullPath, static::$quality);
    }

    /**
     * @param string $src
     * @param string $dest
     */
    public static function copyImg($src, $dest)
    {
        \File::copy($src, $dest);
    }

    /**
     * @param string $filename
     * @param int $formatID
     *
     * @return string
     */
    public static function makeFormatFilenameFromImageFilename($filename, $formatID = MediaImgFormat::THUMBNAIL)
    {
        $extensionPosition = strrpos($filename, ".");

        return sprintf('%s_%s.%s', substr($filename, 0, $extensionPosition),
            MediaImgFormat::getFormatAcronyms($formatID),
            substr($filename, $extensionPosition + 1));
    }

    /**
     * @param string $filename
     * @param string $extension
     * @param int $formatID
     *
     * @return string
     */
    public static function makeFormatFilename($filename, $extension, $formatID = MediaImgFormat::ORIGINAL)
    {
        $suffix = '';
        if ($formatID != MediaImgFormat::ORIGINAL) {
            $suffix .= sprintf('_%s', MediaImgFormat::getFormatAcronyms($formatID));
        }
        return sprintf('%s%s.%s',
            $filename,
            $suffix,
            $extension
        );
    }

}