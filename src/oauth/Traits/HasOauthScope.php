<?php namespace Naraki\Oauth\Traits;

use Illuminate\Database\Eloquent\Builder;
use Naraki\Oauth\Models\OAuthProvider;

trait HasOauthScope
{
    /**
     * @link https://laravel.com/docs/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $wheres
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeOauth(Builder $builder, $wheres = null)
    {
        return $this->joinWithWheres($builder, OAuthProvider::class, $wheres);

    }

}