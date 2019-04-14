<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaGroupRecord extends Model {

	public $timestamps = false;
	protected $primaryKey = 'media_group_record_id';
	protected $fillable = ['media_group_type_id','media_record_id'];



}
