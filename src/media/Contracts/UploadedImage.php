<?php namespace Naraki\Media\Contracts;

interface UploadedImage
{
    public function getUuid();

    public function getTargetType();

    public function getTargetSlug();

}