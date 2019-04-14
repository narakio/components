<?php namespace Naraki\Media\Providers;

use Illuminate\Support\Facades\Log;
use Naraki\Media\Contracts\UploadedImage as ImageContract;
use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Core\EloquentProvider;
use Naraki\Media\Contracts\Avatar as AvatarInterface;
use Naraki\Media\Contracts\Image as ImageInterface;
use Naraki\Media\Models\MediaCategoryRecord;
use Naraki\Media\Models\MediaDigital;
use Naraki\Media\Models\MediaEntity;
use Naraki\Media\Models\MediaImg;
use Naraki\Media\Models\MediaImgFormat;
use Naraki\Media\Models\MediaImgFormatType;
use Naraki\Media\Models\MediaRecord;
use Naraki\Media\Models\MediaType;
use Naraki\Media\Models\Views\EntitiesWithMedia;
use Naraki\Media\Support\ImageProcessor;

/**
 * @method \Naraki\Media\Models\MediaDigital createModel(array $attributes = [])
 */
class Image extends EloquentProvider implements ImageInterface
{
    protected $model = \Naraki\Media\Models\MediaDigital::class;
    /**
     * @var \Naraki\Media\Contracts\Avatar|\Naraki\Media\Providers\Avatar
     */
    private $avatar;

    public function __construct(AvatarInterface $ai, $model = null)
    {
        parent::__construct($model);
        $this->avatar = $ai;
    }

    /**
     * @return \Naraki\Media\Contracts\Avatar|\Naraki\Media\Providers\Avatar
     */
    public function avatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $uuid
     * @param array $columns
     * @return \Naraki\Media\Models\MediaDigital
     */
    public function getOneSimple($uuid, $columns = ['*'])
    {
        return $this->buildOneWithScopes($columns, ['mediaType'], [['media_uuid', $uuid]])->first();
    }

    /**
     * @param string $uuid
     * @param array $columns
     * @return \Naraki\Media\Models\MediaDigital
     */
    public function getOne($uuid, $columns = ['*'])
    {
        return $this->buildOneWithScopes($columns, [
            'mediaType',
            'mediaRecord',
            'mediaCategoryRecord',
            'mediaEntity',
            'entityType'
        ], [['media_uuid', $uuid]])->first();
    }

    /**
     * Gets the image formats that have a record in the database
     * Every formatted image should leave a trace in the db except for the thumbnail and the original files
     * which are always present.
     * As of this writing, avatars are the only image whose originals we don't keep.
     *
     * @param string $uuid
     * @return array
     */
    public function getAvailableFormats($uuid)
    {
        $available = $this->buildOneWithScopes(['media_img_format_id'], ['mediaType', 'formats'],
            [['media_uuid', $uuid]])
            ->pluck('media_img_format_id')->flip()->toArray();

        $defaults = MediaImgFormat::getDimensions();
        $formats = [];
        foreach ($defaults as $const => $dimensions) {
            $formats[$const] = [
                'label' => MediaImgFormat::getConstantName($const, true),
                'dimensions' => $dimensions,
                'exists' => isset($available[$const]) || $const == MediaImgFormat::THUMBNAIL || $const == MediaImgFormat::ORIGINAL
            ];
        }
        return $formats;
    }

    /**
     * @param string $uuid
     * @param array $data
     */
    public function updateOne($uuid, $data)
    {
        $mediaTypeModel = new MediaType();

        $media = $mediaTypeModel->newQuery()->select(['media_type_id'])
            ->where('media_uuid', '=', $uuid)->first();
        if (is_null($media)) {
            return;
        }
        $fillables = $this->filterFillables($data, $mediaTypeModel);
        if (!empty($fillables)) {
            $media->update($fillables);
        }

        $model = $this->createModel();
        $fillables = $this->filterFillables($data, $model);
        if (!empty($fillables)) {
            $model->newQuery()->where('media_type_id', '=', $media->getKey())
                ->update($fillables);
        }
    }

    /**
     * @param \Naraki\Media\Contracts\UploadedImage $image
     * @return int
     */
    public function saveAvatar(ImageContract $image)
    {
        $targetEntityTypeId = $this->saveImageDb($image);
        if (env('APP_ENV') !== 'testing') {
            $this->setAsUsed($image->getUuid(), $targetEntityTypeId);
        }
        return $targetEntityTypeId;
    }

    /**
     * @param string $username
     * @param string $filename
     */
    public function createAvatar($username, $filename)
    {
        $f = new \Naraki\Media\Support\GeneratedAvatar(
            $username,
            $filename,
            \Naraki\Core\Models\Entity::USERS,
            \Naraki\Media\Models\Media::IMAGE_AVATAR
        );
        $f->processAvatar();
        $this->saveAvatar($f);

    }

    /**
     * @param \Naraki\Media\Contracts\UploadedImage $image
     * @return array|int
     */
    private function getTargetEntity(ImageContract $image)
    {
        return EntityType::getEntityTypeID($image->getTargetType(), $image->getTargetSlug());
    }

    /**
     * @param \Naraki\Media\Contracts\UploadedImage $image
     * @param array $formats
     * @return array|int
     */
    public function saveImageDb(ImageContract $image, $formats = [])
    {
        $targetEntityTypeId = $this->getTargetEntity($image);
        if (is_null($targetEntityTypeId)) {
            throw new \UnexpectedValueException(trans('error.media.entity_not_found'));
        }
        $this->createImage($image, $targetEntityTypeId, false, $formats);
        return $targetEntityTypeId;
    }

    /**
     * @param \Naraki\Media\Contracts\UploadedImage|\Naraki\Media\Support\UploadedAvatar $media
     * @param int $entityTypeID
     * @param bool $setAsUsed
     * @param array $formats
     * @return void
     */
    public function createImage($media, $entityTypeID, $setAsUsed = true, $formats = [])
    {
        \DB::transaction(function () use ($media, $entityTypeID, $setAsUsed, $formats) {
            //For now the title of the image is the entity's slug, so we have an idea of which is which in mysql
            $mediaType = MediaType::query()->create([
                'media_title' => $media->getFilename(),
                'media_uuid' => $media->getUuid(),
                'media_id' => $media->getMediaType(),
                'media_in_use' => $setAsUsed
            ]);

            $mediaDigital = MediaDigital::query()->create([
                'media_type_id' => $mediaType->getKey(),
                'media_extension' => $media->getFileExtension(),
                'media_filename' => $media->getFilename(),
            ]);

            $mediaRecord = MediaRecord::query()->create([
                'media_type_id' => $mediaType->getKey(),
            ]);

            $mediaCategoryRecord = MediaCategoryRecord::query()->create([
                'media_record_target_id' => $mediaRecord->getKey(),
            ]);

            MediaEntity::query()->create([
                'entity_type_id' => $entityTypeID,
                'media_category_record_id' => $mediaCategoryRecord->getKey(),
            ]);

            MediaImg::query()->create([
                'media_digital_id' => $mediaDigital->getKey()
            ]);
            $this->addFormatReferences($formats, $mediaDigital->getKey());
        });
    }

    /**
     * @param array $formats
     * @param int $mediaDigitalId
     * @return void
     */
    public function addFormatReferences(array $formats, int $mediaDigitalId)
    {
        foreach ($formats as $format) {
            MediaImgFormatType::query()->create([
                'media_digital_id' => $mediaDigitalId,
                'media_img_format_id' => $format
            ]);
        }
    }

    /**
     * @param int|array $entityTypeId
     * @param array $columns
     * @param bool $inUse
     * @return \Naraki\Media\Models\MediaEntity[]
     */
    public function getImages($entityTypeId, $columns = null, $inUse = false)
    {
        return $this->buildImages($entityTypeId, $columns, $inUse)->get();
    }

    /**
     * @param $entityTypeId
     * @param null $columns
     * @param bool $inUse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildImages($entityTypeId, $columns = null, $inUse = false)
    {
        if (is_null($columns)) {
            $columns = $this->getDefaultImageColumns();
        }
        return MediaEntity::buildImages($entityTypeId, $columns, $inUse);
    }

    /**
     * @param string $slug
     * @param int $entityId
     * @param array $columns
     * @return \Naraki\Media\Models\MediaEntity[]
     */
    public function getImagesFromSlug($slug, $entityId = Entity::BLOG_POSTS, $columns = null, $entityTypeId = null)
    {
        if (is_null($columns)) {
            $columns = [
                'media_uuid as uuid',
                'media_in_use as used',
                'media_extension as ext',
                'media_title as title',
                \DB::raw(
                    sprintf(
                        '"%s" as suffix',
                        MediaImgFormat::getFormatAcronyms(MediaImgFormat::THUMBNAIL)
                    )
                )
            ];
        }
        if (is_null($entityTypeId)) {
            $entityTypeId = EntityType::getEntityTypeID($entityId, $slug);
        }
        return $this->getImages($entityTypeId, $columns);
    }

    /**
     * @param string $uuid
     * @param int $entityTypeId
     * @return bool
     */
    public static function setAsUsed($uuid, $entityTypeId)
    {
        if (is_img_uuid_string($uuid)) {
            return \DB::unprepared(sprintf('CALL sp_update_media_type_in_use("%s",%s)', $uuid, $entityTypeId));
        }
        throw new \UnexpectedValueException('uuid is not valid');
    }

    /**
     * @param int|array $entityTypeId
     * @param int $entityId
     * @param int $mediaType
     * @throws \Exception
     */
    public function deleteByEntity($entityTypeId, $entityId, $mediaType = \Naraki\Media\Models\Media::IMAGE)
    {
        $media = $this->getImages($entityTypeId, ['media_uuid', 'media_extension']);

        if (!is_null($media)) {
            foreach ($media as $record) {
                $this->deleteFiles(
                    $entityId,
                    $mediaType,
                    $record->getAttribute('media_uuid'),
                    $record->getAttribute('media_extension')
                );
            }
        }
    }

    /**
     * @param string|array $uuid
     * @param int $entityId
     * @param int $mediaType
     * @throws \Exception
     */
    public function delete($uuid, $entityId, $mediaType = \Naraki\Media\Models\Media::IMAGE)
    {
        /** @var \Naraki\Media\Models\MediaType $media */
        $builder = MediaType::query()
            ->select([
                'media_types.media_type_id',
                'media_uuid',
                'media_extension'
            ])->mediaDigital();
        $media = null;
        if (is_string($uuid)) {
            if (is_img_uuid_string($uuid)) {
                $media = $builder->where('media_uuid', '=', $uuid)
                    ->get();
            }
        } elseif (is_array($uuid)) {
            $media = $builder->whereIn('media_uuid', $uuid)
                ->get();
        } else {
            throw new \UnexpectedValueException('uuid is not valid');
        }
        if (!is_null($media)) {
            foreach ($media as $record) {
                $this->deleteFiles(
                    $entityId,
                    $mediaType,
                    $record->getAttribute('media_uuid'),
                    $record->getAttribute('media_extension')
                );
                $record->delete();
            }
        }
    }

    /**
     * @param int $entityId
     * @param int $imageType
     * @param string $uuid
     * @param string $fileExtension
     */
    public function deleteFiles($entityId, $imageType, $uuid, $fileExtension)
    {
        $formats = MediaImgFormat::getFormatAcronyms();
        foreach ($formats as $format) {
            $suffix = '';
            if (!empty($format)) {
                $suffix .= sprintf('_%s', $format);
            }
            @\File::delete(
                media_entity_root_path(
                    $entityId,
                    $imageType,
                    sprintf('%s%s.%s', $uuid, $suffix, $fileExtension)
                )
            );
        }
    }

    /**
     * @param string $uuid
     * @param int $entityId
     * @param int $imageType
     * @param string $fileExtension
     * @param int $format
     */
    public function cropImageToFormat($uuid, $entityId, $imageType, $fileExtension, $format = MediaImgFormat::THUMBNAIL)
    {
        ImageProcessor::saveImg(
            ImageProcessor::makeCroppedImage(
                media_entity_root_path(
                    $entityId,
                    $imageType,
                    ImageProcessor::makeFormatFilename(
                        $uuid,
                        $fileExtension
                    )
                ),
                $format
            ),
            media_entity_root_path(
                $entityId,
                $imageType,
                ImageProcessor::makeFormatFilename($uuid, $fileExtension, $format)
            )
        );
    }

    /**
     *  Get images that are attached to the same entity, i.e. all images that are used in a blog post.
     *
     * @param $uuid
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSiblings($uuid, $columns = ['*'])
    {
        return EntitiesWithMedia::getSiblings($uuid, $columns);
    }

    public function getDefaultImageColumns()
    {
        return [
            'media_uuid as uuid',
            'media_in_use as used',
            'media_extension as ext',
            'media_title as title',
            \DB::raw(
                sprintf(
                    '"%s" as suffix',
                    MediaImgFormat::getFormatAcronyms(MediaImgFormat::THUMBNAIL)
                )
            ),
        ];
    }
}
