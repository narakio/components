<?php namespace Naraki\System\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Naraki\Sentry\Models\User;

class SystemUserSubscriptions extends Model
{
    use DoesSqlStuff;
    protected $primaryKey = 'system_user_subscription_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'system_event_type_id',
        'system_event_id'
    ];

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeSystemEvent(Builder $builder)
    {
        return $this->joinReverse($builder, SystemEvent::class);
    }

    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeUser(Builder $builder)
    {
        return User::scopeGenericPerson($this->joinReverse($builder, User::class));
    }

}
