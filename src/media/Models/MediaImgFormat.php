<?php namespace Naraki\Media\Models;

use Naraki\Media\Support\ImageProcessor;
use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class MediaImgFormat extends Model
{
    use Enumerable;

    const ORIGINAL = 1;
    const THUMBNAIL = 2;
    const FEATURED = 3;
    const HD = 4;
    const FHD = 5;
    public $timestamps = false;

    protected $primaryKey = 'media_img_format_id';
    protected $fillable = [
        'media_img_format_name',
        'media_img_format_width',
        'media_img_format_height'
    ];

    /**
     * @param int $formatID
     * @return array|\stdClass
     */
    public static function getFormatDimensions($formatID = null)
    {
        $formats = static::getDimensions();
        if (is_null($formatID)) {
            return $formats;
        } else {
            return $formats[$formatID];
        }
    }

    /**
     * @param bool $pretty
     * @return array
     */
    public static function getDimensions($pretty = false)
    {
        return [
            (($pretty ? self::getConstantName(static::ORIGINAL, $pretty) : static::ORIGINAL)) => (object)[
                'width' => 0,
                'height' => 0
            ],
            (($pretty ? self::getConstantName(static::THUMBNAIL, $pretty) : static::THUMBNAIL)) => (object)[
                'width' => ImageProcessor::$thumbnailWidth,
                'height' => ImageProcessor::$thumbnailHeight
            ],
            (($pretty ? self::getConstantName(static::FEATURED, $pretty) : static::FEATURED)) => (object)[
                'width' => 500,
                'height' => 300
            ],
            (($pretty ? self::getConstantName(static::HD, $pretty) : static::HD)) => (object)[
                'width' => 1280,
                'height' => 720
            ],
            (($pretty ? self::getConstantName(static::FHD, $pretty) : static::FHD)) => (object)[
                'width' => 1920,
                'height' => 1080
            ],
        ];
    }

    /**
     * @param int $formatID
     * @return array|string
     */
    public static function getFormatAcronyms($formatID = null)
    {
        $formats = [
            static::ORIGINAL => '',
            static::THUMBNAIL => 'tb',
            static::FEATURED => 'ft',
            static::HD => 'hd',
            static::FHD => 'hf',
        ];
        if (is_null($formatID)) {
            return $formats;
        }
        return $formats[$formatID];
    }

}
