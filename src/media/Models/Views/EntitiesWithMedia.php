<?php namespace Naraki\Media\Models\Views;

use Illuminate\Database\Eloquent\Model;

class EntitiesWithMedia extends Model
{
    protected $table = 'entities_with_media';
    protected $primaryKey = 'entity_type_id';

    public static function getSiblings($uuid, $columns = ['*'])
    {
        return static::query()->select($columns)->whereIn('entity_type_id', function ($query) use ($uuid) {
            $query->select('entity_type_id')->from('entities_with_media')
                ->where('media_uuid', '=', $uuid);
        });
    }

}