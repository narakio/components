<?php

use Illuminate\Database\Seeder;
use Naraki\Media\Support\ImageProcessor;
use Naraki\Media\Models\MediaImgFormat;

class ImageSeeder extends Seeder
{
    private $origDir = __DIR__ . '/../files/data/img';

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $images = $this->parseImages();
        $posts = [];
//        dd($images);

        $postSlugIds = \Naraki\Blog\Models\BlogPost::query()->select([
            'blog_post_slug',
            'blog_post_id',
            'entity_types.entity_type_id'
        ])
            ->entityType()
            ->get();
        foreach ($postSlugIds as $slug) {
            $posts[$slug['blog_post_slug']] = (object)[
                'entity' => $slug['entity_type_id'],
                'post' => $slug['blog_post_id']
            ];
        }
        $p = 0;
        foreach ($images as $image => $extension) {
            if (isset($posts[$image])) {
                if($p>50){
                    return;
                }
                $p++;
                $uuid = makeHexUuid();
                $uuid=sprintf('%s_%s', substr($image, 0, 31), makeHexUuid());
                $this->saveImage(
                    sprintf('%s/%s.%s', $this->origDir, $image, $extension),
                    $extension,
                    $uuid
                );
                \DB::beginTransaction();
                $this->saveDb($uuid, $extension, $posts[$image]->entity,
                    [MediaImgFormat::FEATURED,MediaImgFormat::ORIGINAL]);
                \DB::commit();
            }
        }
    }

    private function saveDb($uuid, $fileExtension, $entityTypeID, $formats)
    {
        $filename = sprintf('%s.%s', $uuid, $fileExtension);
        $mediaType = \Naraki\Media\Models\MediaType::create([
            'media_title' => $filename,
            'media_uuid' => $uuid,
            'media_id' => \Naraki\Media\Models\Media::IMAGE,
            'media_in_use' => 1
        ]);

        $mediaDigital = \Naraki\Media\Models\MediaDigital::create([
            'media_type_id' => $mediaType->getKey(),
            'media_extension' => $fileExtension,
            'media_filename' => $filename,
        ]);

        $mediaRecord = \Naraki\Media\Models\MediaRecord::create([
            'media_type_id' => $mediaType->getKey(),
        ]);

        $mediaCategoryRecord = \Naraki\Media\Models\MediaCategoryRecord::create([
            'media_record_target_id' => $mediaRecord->getKey(),
        ]);

        \Naraki\Media\Models\MediaEntity::create([
            'entity_type_id' => $entityTypeID,
            'media_category_record_id' => $mediaCategoryRecord->getKey(),
        ]);

        \Naraki\Media\Models\MediaImg::create([
            'media_digital_id' => $mediaDigital->getKey()
        ]);

        if (!is_null($formats)) {
            foreach ($formats as $format) {
                \Naraki\Media\Models\MediaImgFormatType::create([
                    'media_digital_id' => $mediaDigital->getKey(),
                    'media_img_format_id' => $format
                ]);
            }
        }
    }

    private function saveImage($path, $fileExtension, $uuid)
    {

        ImageProcessor::saveImg(
            ImageProcessor::makeCroppedImage($path),
            media_entity_root_path(
                \Naraki\Core\Models\Entity::BLOG_POSTS,
                \Naraki\Media\Models\Media::IMAGE,
                ImageProcessor::makeFormatFilenameFromImageFilename(
                    sprintf('%s.%s', $uuid, $fileExtension)
                )
            )
        );

        ImageProcessor::saveImg(
            ImageProcessor::makeCroppedImage(
                $path,
                \Naraki\Media\Models\MediaImgFormat::FEATURED
            ),
            media_entity_root_path(
                \Naraki\Core\Models\Entity::BLOG_POSTS,
                \Naraki\Media\Models\Media::IMAGE,
                ImageProcessor::makeFormatFilename($uuid, $fileExtension, \Naraki\Media\Models\MediaImgFormat::FEATURED)
            )
        );

//        ImageProcessor::saveImg(
//            ImageProcessor::makeCroppedImage(
//                $path,
//                \Naraki\Media\Models\MediaImgFormat::HD
//            ),
//            media_entity_root_path(
//                \Naraki\Core\Models\Entity::BLOG_POSTS,
//                \Naraki\Media\Models\Media::IMAGE,
//                ImageProcessor::makeFormatFilename($uuid, $fileExtension, \Naraki\Media\Models\MediaImgFormat::HD)
//            )
//        );

        ImageProcessor::copyImg(
            $path,
            media_entity_root_path(
                \Naraki\Core\Models\Entity::BLOG_POSTS,
                \Naraki\Media\Models\Media::IMAGE,
                ImageProcessor::makeFormatFilename($uuid, $fileExtension, \Naraki\Media\Models\MediaImgFormat::ORIGINAL)
            )
        );

    }

    private function seedChunk($data, $model, $nbChunks = 25)
    {
        $chunks = array_chunk($data, $nbChunks);
        foreach ($chunks as $chunk) {
            forward_static_call(sprintf('%s::insert', $model), $chunk);
        }
    }

    private function parseImages()
    {
        $dir = opendir($this->origDir);
        if (!$dir) {
            die(sprintf("%s could not be read.", $this->origDir));
        }
        //We wanna insert products in the database before the rest because of the integrity checks.
        $images = [];
        while (($file = readdir($dir)) !== false) {
            $fullPath = $this->origDir . '/' . $file;
            if (is_file($fullPath)) {
                $pos = strrpos($file, '.');
                $images[substr($file, 0, $pos)] = substr($file, $pos + 1);
            }
        }
        closedir($dir);
        return $images;
    }


}
