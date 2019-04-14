<?php namespace Naraki\Mail\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailUserEventFail extends Model
{
    protected $primaryKey = 'email_user_fail_id';
    protected $fillable = [
        'email_user_event_id',
        'email_user_fail_message',
        'email_user_fail_code',
        'email_user_fail_description',
        'email_user_fail_timestamp',
    ];
    public $timestamps = false;

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEmailUserEvent(Builder $query)
    {
        return $query->join('email_user_events', 'email_user_event_fails.email_user_event_id', '=', 'email_user_events.email_user_event_id');
    }


    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUser(Builder $query)
    {
        return $query->join('users', 'users.user_id', '=', 'email_user_events.user_id');
    }

}