<?php namespace Naraki\Media\Models;

use Naraki\Core\Traits\Models\DoesSqlStuff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaDigital extends Model
{
    use DoesSqlStuff;

    const MEDIA_DIGITAL_MAX_FILESIZE = 2;

    protected $table = 'media_digital';
    protected $primaryKey = 'media_digital_id';
    protected $fillable = ['media_type_id', 'media_filename', 'media_extension', 'media_alt', 'media_caption'];
    protected $hidden = ['media_digital_id', 'media_type_id'];

    /**
     * @param string $value
     * @return string
     */
    public function getCreatedAtPrettyAttribute($value)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(get_locale_date_format());
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeMediaType(Builder $builder)
    {
        return $this->joinReverse($builder, MediaType::class);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeFormats(Builder $builder)
    {
        return $this->join($builder, MediaImgFormatType::class);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeMediaRecord(Builder $builder)
    {
        return $this->joinScope($builder,'media_records','media_types','media_type_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $mediaCategoryId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeMediaCategoryRecord(Builder $builder, $mediaCategoryId = MediaCategory::MEDIA)
    {
        return $builder->join(
            'media_category_records',
            'media_category_records.media_record_target_id',
            '=',
            'media_records.media_type_id'
        )->where('media_category_records.media_category_id', $mediaCategoryId);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeMediaEntity(Builder $builder)
    {
        return $this->joinScope(
            $builder,
            'media_entities',
            'media_category_records',
            'media_category_record_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder)
    {
        return $this->joinScope($builder,'entity_types','media_entities','entity_type_id');
    }

}