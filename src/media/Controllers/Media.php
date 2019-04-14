<?php namespace Naraki\Media\Controllers;

use Naraki\Core\Controllers\Admin\Controller;
use Naraki\Sentry\Jobs\UpdateUserElasticsearch;
use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Sentry\Providers\User as UserProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Naraki\Media\Facades\Media as MediaProvider;
use Naraki\Media\Filters\Media as MediaFilter;
use Naraki\Media\Models\Media as MediaModel;
use Naraki\Media\Models\MediaDigital;
use Naraki\Media\Models\MediaImgFormat;
use Naraki\Media\Requests\Update as UpdateMedia;
use Naraki\Media\Support\ImageProcessor;
use Naraki\Media\Support\UploadedAvatar;
use Naraki\Media\Support\UploadedImage;

class Media extends Controller
{
    use DispatchesJobs;

    public function index(MediaFilter $filter)
    {
        MediaProvider::setStoredFilter(Entity::SYSTEM, $this->user->getKey(), $filter);
        $media = MediaProvider::getMedia([
            'media_title',
            'media_extension',
            'media_uuid',
            'media_digital.created_at as created_ago',
            'media_digital.created_at'
        ])->whereNotIn('media.media_id', [MediaModel::IMAGE_AVATAR])
            ->filter($filter);

        return response([
            'table' => $media->paginate(25),
            'sorted' => $filter->getFilter('sortBy'),
            'columns' => MediaProvider::createModel()->getColumnInfo(
                [
                    'media_title' => (object)[
                        'name' => trans('js-backend.db.media_title'),
                    ],
                    'created_ago' => (object)[
                        'name' => trans('js-backend.db.media_created_at'),
                    ]
                ]
                , $filter)
        ], Response::HTTP_OK);

    }

    /**
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function add()
    {
        $input = (object)app('request')->only(['type', 'target', 'media', 'group', 'category', 'file']);

        $file = app('request')->file('file');

        if (!is_null($file)) {
            $fileSize = $file->getSize();
            if ($fileSize < MediaDigital::MEDIA_DIGITAL_MAX_FILESIZE * 1000000) {
                try {
                    //Type is users, forum posts, etc.
                    if (!Entity::isValidName($input->type)) {
                        throw new \UnexpectedValueException(
                            trans('error.media.entity_type', ['type' => $input->type])
                        );
                    }
                    //Media is image, image_avatar, etc.
                    if (!MediaModel::isValidName($input->media)) {
                        throw new \UnexpectedValueException(
                            trans('error.media.media_type', ['type' => $input->media])
                        );
                    }

                    switch ($input->media) {
                        case "image_avatar":
                            $media = new UploadedAvatar(
                                $file,
                                $input->target,
                                $input->type,
                                $input->media
                            );
                            $media->saveTemporaryAvatar();
                            break;
                        case "image":
                            $media = new UploadedImage(
                                $file,
                                $input->target,
                                $input->type,
                                $input->media
                            );
                            return response(
                                MediaProvider::image()->getImages(
                                    $this->processImage($media))->toArray(),
                                Response::HTTP_OK
                            );
                            break;
                        default:
                            return response(trans(
                                'error.media.wrong_type'),
                                Response::HTTP_INTERNAL_SERVER_ERROR
                            );
                    }
                } catch (\Exception $e) {
                    return response([
                        'data' => trans('error.media.type_size'),
                        'error' => $e->getMessage()
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                return response([
                    'filename' => $media->getHddFilename(),
                ], Response::HTTP_OK);
            }
            return response([
                trans('js-common.dropzone.file_too_big_laravel', [
                    'filesize' => round($fileSize / 1000000, 2),
                    'maxFilesize' => MediaDigital::MEDIA_DIGITAL_MAX_FILESIZE
                ])
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response([
            trans('error.media.no_file')
        ], Response::HTTP_INTERNAL_SERVER_ERROR);

    }

    public function edit($entity, $uuid)
    {
        $query = MediaProvider::image()->getOne($uuid,
            [
                'media_uuid',
                'media_title',
                'media_alt',
                'created_at as created_at_pretty',
                'media_description',
                'media_caption',
                'media_id',
                'media_extension',
                'entity_id',
                'entity_types.entity_type_id'
            ]
        );
        if (is_null($query)) {
            return response(trans('error.http.500.general_retrieval_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $media = $query->toArray();

        if ($entity === 'media') {
            $filter = MediaProvider::getStoredFilter(Entity::SYSTEM, $this->user->getKey());
            $data = MediaProvider::getMedia([
                'media_uuid',
                'entity_types.entity_type_id',
                'entity_id'
            ])->whereNotIn('media.media_id', [MediaModel::IMAGE_AVATAR])
                ->filter($filter)->get();
        } else {
            $data = MediaProvider::image()
                ->getSiblings($uuid, ['media_uuid', 'entity_type_id', 'entity_id'])
                ->get();
        }

        $formats = MediaProvider::image()->getAvailableFormats($uuid);

        $navData = $data->pluck('media_uuid')->all();
        $media['media'] = MediaModel::getConstantName($media['media_id']);
        $entityId = $query->getAttribute('entity_id');
        $media['entity'] = Entity::getConstantName($entityId);
        $media['entity_pretty'] = Entity::getConstantName($entityId, true);
        $media['suffix'] = MediaImgFormat::getFormatAcronyms(MediaImgFormat::THUMBNAIL);
        $entityModel = Entity::createModel($entityId);
        $entityData = EntityType::buildQueryFromEntity($entityId, $media['entity_type_id'])
            ->select([$entityModel->getPrettyName(), $entityModel->getSlugName()])
            ->first();
        $media['title'] = $entityData->getPretty();
        $media['slug'] = $entityData->getSlug();

        unset($media['entity_type_id'], $media['media_id'], $media['entity_id']);
        $index = array_search($uuid, $navData);
        $total = count($navData);
        $nav = [
            'total' => $total,
            'idx' => ($index + 1),
            'first' => isset($navData[0]) ? $navData[0] : null,
            'last' => $navData[($total - 1)],
            'prev' => isset($navData[$index - 1]) ? ($navData[$index - 1] ?? null) : null,
            'next' => isset($navData[$index + 1]) ? ($navData[$index + 1] ?? null) : null
        ];
        return response(compact('media', 'nav', 'formats'));
    }

    /**
     *
     * @param string $uuid
     * @param \Naraki\Media\Requests\Update $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update($uuid, UpdateMedia $request)
    {
        if (is_img_uuid_string($uuid)) {
            MediaProvider::image()->updateOne($uuid, $request->all());
        }
        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Naraki\Media\Support\UploadedImage $media
     * @return int
     * @throws \Exception
     * @throws \Throwable
     */
    private function processImage($media)
    {
        $media->move();
        $media->makeThumbnail();
        MediaProvider::image()->cropImageToFormat(
            $media->getUuid(),
            $media->getTargetType(),
            $media->getMediaType(),
            $media->getFileExtension(),
            MediaImgFormat::FEATURED
        );
        MediaProvider::image()->cropImageToFormat(
            $media->getUuid(),
            $media->getTargetType(),
            $media->getMediaType(),
            $media->getFileExtension(),
            MediaImgFormat::HD
        );
        return MediaProvider::image()->saveImageDb(
            $media,
            [
                MediaImgFormat::FEATURED,
                MediaImgFormat::HD
            ]
        );
    }

    /**
     * @param \Naraki\Sentry\Providers\User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function cropAvatar(UserProvider $user)
    {
        $input = (object)app('request')->only(['uuid', 'height', 'width', 'x', 'y']);

        /**
         * @var \Naraki\Media\Support\SimpleUploadedImage $avatarInfo
         */
        $avatarInfo = Cache::get('temporary_avatars')->pull(
            substr($input->uuid, 0, strrpos($input->uuid, '.')));
        try {
            $avatarInfo->cropAvatar($input);
            MediaProvider::image()->saveAvatar($avatarInfo);
        } catch (\Exception $e) {
            return response([
                'data' => trans('error.media.type_size'),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->dispatch(new UpdateUserElasticsearch(
                UpdateUserElasticsearch::WRITE_MODE_UPDATE,
                $this->user->getKey())
        );
        return response($user->getAvatars($this->user->getKey()), Response::HTTP_OK);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function crop()
    {
        $input = (object)app('request')->only(['uuid', 'format', 'height', 'width', 'x', 'y']);

        $data = MediaProvider::getMedia([
            'media_digital.media_digital_id',
            'entity_types.entity_type_id',
            'entity_id',
            'media_extension'
        ])->where('media_uuid', $input->uuid)->first();
        if (!is_null($data)) {
            $imagePath = media_entity_root_path(
                $data->getAttribute('entity_id'),
                'image',
                sprintf(
                    '%s_%s.%s',
                    $input->uuid,
                    MediaImgFormat::getFormatAcronyms($input->format),
                    $data->getAttribute('media_extension')
                )
            );
            $sourceImageForCroppingPath = media_entity_root_path(
                $data->getAttribute('entity_id'),
                'image',
                sprintf(
                    '%s.%s',
                    $input->uuid,
                    $data->getAttribute('media_extension')
                )
            );

            if (is_file($sourceImageForCroppingPath)) {
                ImageProcessor::saveImg(
                    ImageProcessor::nipNTuck($sourceImageForCroppingPath, $input, $input->format),
                    $imagePath
                );
                MediaProvider::image()->addFormatReferences(
                    [$input->format],
                    $data->getAttribute('media_digital_id')
                );
            } else {
                throw new \UnexpectedValueException(
                    'The original image used for cropping does not exist: ' . $sourceImageForCroppingPath
                );
            }
        }
        $msg = trans(
            'messages.media_crop_success',
            ['format' => MediaImgFormat::getConstantName($input->format, true)]
        );
        $formats = MediaProvider::image()->getAvailableFormats($input->uuid);
        return response(compact('formats', 'msg'));
    }


}