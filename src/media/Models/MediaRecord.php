<?php namespace Naraki\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaRecord extends Model {

	public $timestamps = false;
	protected $fillable = ['media_type_id'];
	protected $primaryKey = 'media_record_id';
	
	/**
	 * @link https://laravel.com/docs/5.2/eloquent#query-scopes
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeMediaCategoryRecord(Builder $query)
	{
		return $query->join('media_category_records', 'media_category_records.media_category_record_target_id', '=', 'media_records.media_record_id');
	}

	/**
	 * @link https://laravel.com/docs/5.2/eloquent#query-scopes
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeMediaSystemEntity(Builder $query)
	{
		return $query->join('media_system_entities', 'media_category_records.media_category_record_id', '=', 'media_system_entities.media_category_record_id');
	}
	
	

}
