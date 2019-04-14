<?php namespace Naraki\Mail\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class EmailSchedule extends Model
{
    protected $primaryKey = 'email_schedule_id';
    protected $fillable = [
        'email_schedule_source_id',
        'email_schedule_name',
        'email_list_id',
        'email_schedule_send_at',
        'email_schedule_periodicity',
    ];
    public $timestamps = false;

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $entityID
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmailList(Builder $query, $entityID = null)
    {
        return $query->join('email_lists', function (JoinClause $q) use ($entityID) {
            $q->on('email_lists.email_list_id', '=', 'email_schedules.email_list_id');
            if (!is_null($entityID)) {
                $q->where('entity_id', '=', $entityID);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $targetID
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeSourceEntityType(Builder $query, $targetID = null)
    {
        return $query->join('entity_types', function (JoinClause $q) use ($targetID) {
            $q->on('email_schedules.email_schedule_source_id',
                '=',
                'entity_types.entity_type_id'
            );
            if (!is_null($targetID)) {
                $q->where('entity_type_target_id', '=', $targetID);
            }
        });
    }
}
