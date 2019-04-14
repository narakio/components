<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaImg extends Model
{
    protected $table = 'media_img';
    protected $primaryKey = 'media_img_id';
    protected $fillable = ['media_digital_id', 'media_img_attribution'];
    public $timestamps = false;
}
