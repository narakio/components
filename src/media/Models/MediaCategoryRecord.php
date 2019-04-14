<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaCategoryRecord extends Model
{

    public $timestamps = false;
    protected $primaryKey = 'media_category_record_id';
    protected $fillable = ['media_category_id', 'media_record_target_id'];



}
