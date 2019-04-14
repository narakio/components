<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaImgFormatType extends Model
{
    protected $primaryKey = 'media_img_format_type_id';
    protected $fillable = ['media_digital_id', 'media_img_format_id'];
    public $timestamps = false;
}
