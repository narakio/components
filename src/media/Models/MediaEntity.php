<?php namespace Naraki\Media\Models;

use Naraki\Core\Contracts\HasAnEntity as HasAnEntityContract;
use Naraki\Core\Traits\Enumerable;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Naraki\Core\Traits\Models\HasAnEntity as HasAnEntity;
use Naraki\Core\Traits\Models\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Naraki\Media\Support\Presenters\MediaEntity as MediaEntityPresenter;
use Naraki\Core\Contracts\Enumerable as EnumerableContract;
use Naraki\Permission\Contracts\HasPermissions as HasPermissionsContract;
use Naraki\Permission\Traits\HasPermissions;

class MediaEntity extends Model implements EnumerableContract, HasPermissionsContract, HasAnEntityContract
{
    use DoesSqlStuff, Presentable, Enumerable, HasPermissions, HasAnEntity;

    const PERMISSION_VIEW = 0b1;
    const PERMISSION_ADD = 0b10;
    const PERMISSION_EDIT = 0b100;
    const PERMISSION_DELETE = 0b1000;

    public $timestamps = false;
    protected $presenter = MediaEntityPresenter::class;
    protected $table = 'media_entities';
    protected $primaryKey = 'media_entity_id';
    protected $fillable = ['entity_type_id', 'media_category_record_id'];
    protected $sortable = ['media_title', 'created_ago'];
    public static $entityID = \Naraki\Core\Models\Entity::MEDIA;

    /**
     * Presentable created_at column
     *
     * @param $value
     * @return string
     */
    public function getCreatedAgoAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
    }

    /**
     * Sets the media as being used by a specific entity (user, forum thread etc.)
     * We call a stored procedure that sets all other media from that entity to "not used",
     * because this method is called by entities that can only use one media at a time
     * (i.e profile picture in users, featured image in blog posts, etc.)
     *
     * @param $mediaEntityId
     */
    public static function setMediaAsUsed($mediaEntityId)
    {
        $model = new static();
        $model->newQuery()->where($model->getKeyName(), $mediaEntityId)->update(['media_entity_in_use' => true]);
        \DB::unprepared(sprintf('CALL sp_update_media_entity_in_use(%s)', $mediaEntityId));
    }

    /**
     * @param int $entityTypeId
     * @param array $columns
     * @param array $inUse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function buildImages($entityTypeId = null, $columns = ['*'], $inUse = null)
    {
        return static::query()->select($columns)
            ->entityType($entityTypeId)
            ->scopes([
                'mediaCategoryRecord',
                'mediaRecord',
                'mediaType' => $inUse,
                'mediaDigital'
            ]);
    }

    /**
     * @param int $entityId
     * @param int $mediaId
     * @param int $mediaImgFormatId
     * @return string
     */
    public function asset(int $entityId, int $mediaId, int $mediaImgFormatId): string
    {
        if ($mediaImgFormatId !== MediaImgFormat::ORIGINAL) {
            return media_entity_path(
                $entityId,
                $mediaId,
                sprintf(
                    '%s_%s.%s',
                    $this->getAttribute('uuid'),
                    MediaImgFormat::getFormatAcronyms($mediaImgFormatId),
                    $this->getAttribute('ext')
                )
            );
        }
        return media_entity_path(
            $entityId,
            $mediaId,
            sprintf(
                '%s.%s',
                $this->getAttribute('uuid'),
                $this->getAttribute('ext')
            )
        );
    }

    /**
     * @param int $entityId
     * @param int $mediaId
     * @param string $uuid
     * @param string $ext
     * @param int $mediaImgFormatId
     * @return string
     */
    public static function assetStatic(
        int $entityId,
        int $mediaId,
        string $uuid,
        string $ext,
        int $mediaImgFormatId = MediaImgFormat::THUMBNAIL
    ): string {
        return media_entity_path(
            $entityId,
            $mediaId,
            ($mediaImgFormatId !== MediaImgFormat::ORIGINAL)
                ? sprintf(
                '%s_%s.%s',
                $uuid,
                MediaImgFormat::getFormatAcronyms($mediaImgFormatId),
                $ext
            )
                : sprintf(
                '%s.%s',
                $uuid,
                $ext
            )
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|array $entityTypeId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder, $entityTypeId = null)
    {
        return $builder->join('entity_types', function (JoinClause $q) use ($entityTypeId) {
            $q->on('entity_types.entity_type_id', '=', 'media_entities.entity_type_id');
            if (!is_null($entityTypeId)) {
                if (!is_array($entityTypeId)) {
                    $q->where('entity_types.entity_type_id', '=', $entityTypeId);
                } else {
                    $q->whereIn('entity_types.entity_type_id', $entityTypeId);
                }
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $mediaCategoryId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMediaCategoryRecord(Builder $builder, $mediaCategoryId = MediaCategory::MEDIA)
    {
        return $builder->join('media_category_records', function (JoinClause $q) use ($mediaCategoryId) {
            $q->on('media_entities.media_category_record_id',
                '=',
                'media_category_records.media_category_record_id'
            );
            $q->where('media_category_records.media_category_id', '=', $mediaCategoryId);
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMediaRecord(Builder $builder)
    {
        return $builder->join('media_records',
            'media_category_records.media_record_target_id',
            '=',
            'media_records.media_record_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param bool $inUse
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMediaType(Builder $builder, $inUse = false)
    {
        return $builder->join('media_types', function (JoinClause $q) use ($inUse) {
            $q->on('media_types.media_type_id',
                '=',
                'media_records.media_type_id'
            );
            if ($inUse) {
                $q->where('media_types.media_in_use', '=', 1);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMediaDigital(Builder $builder)
    {
        return $builder->join('media_digital',
            'media_digital.media_type_id', '=', 'media_types.media_type_id');
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMediaEntities(Builder $builder)
    {
        return $builder->join('media_entities',
            'media_entities.entity_type_id',
            '=',
            'entity_types.entity_type_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeMedia(Builder $builder)
    {
        return $builder->join('media',
            'media_types.media_id',
            '=',
            'media.media_id'
        );
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param bool $inUse
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public static function scopeImage($builder, $inUse = false)
    {
        return self::scopeMediaDigital(
            self::scopeMediaType(
                self::scopeMediaRecord(
                    self::scopeMediaCategoryRecord(
                        self::scopeMediaEntities($builder))), $inUse)
        );
    }
}
