<?php namespace Naraki\Oauth\Models;

use Naraki\Sentry\Models\User;
use Naraki\Core\Traits\Models\DoesSqlStuff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OAuthProvider extends Model
{
    use DoesSqlStuff;

    protected $table = 'system_oauth_providers';
    protected $primaryKey = 'oauth_provider_id';
    protected $guarded = ['oauth_provider_id'];
    protected $fillable = [
        'user_id',
        'provider_user_id',
        'provider',
        'access_token',
        'refresh_token',
        'created_at'
    ];

    /**
     * @link https://laravel.com/docs/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeUser(Builder $builder)
    {
        return $this->joinReverse($builder, User::class);
    }
}
