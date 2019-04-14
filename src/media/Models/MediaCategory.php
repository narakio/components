<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaCategory extends Model
{
    const MEDIA = 1;
    const MEDIA_GROUP = 2;

    public $timestamps = false;
    protected $primaryKey = 'media_category_id';
    protected $fillable = ['media_category_name'];

}
