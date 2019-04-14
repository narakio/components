<?php namespace Naraki\Sentry\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatUser extends Model
{
    protected $primaryKey = 'stat_user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'stat_users_last_visit',
    ];

    /**
     * @link https://laravel.com/docs/5.2/eloquent#query-scopes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUser(Builder $query)
    {
        return $query->join('users', 'users.user_id', '=', 'stat_users.user_id');
    }

}