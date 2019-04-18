<?php namespace Naraki\Media\Support\Presenters;

use Naraki\Core\Presenter;
use Naraki\Media\Models\Media;
use Naraki\Media\Models\MediaImgFormat;

/**
 * @see \Naraki\Media\Models\MediaEntity
 */
class MediaEntity extends Presenter
{
    /**
     * @var \Naraki\Media\Models\MediaEntity
     */
    protected $entity;
    public function asset($media = Media::IMAGE, $format = MediaImgFormat::FEATURED)
    {
        return $this->entity->asset(
            $this->entity->getAttribute('entity_id'),
            $media,
            $format
        );
    }

    public function thumbnail($media = Media::IMAGE)
    {
        $entity = $this->entity->getAttribute('entity_id');
        if(is_int($entity)){

        return $this->entity->asset(
            $this->entity->getAttribute('entity_id'),
            $media,
            MediaImgFormat::THUMBNAIL
        );
        }
        return asset(sprintf('/media/img/site/placeholder_%s.png',\Naraki\Media\Models\MediaImgFormat::getFormatAcronyms(\Naraki\Media\Models\MediaImgFormat::FEATURED)));
    }

}