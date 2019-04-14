<?php namespace Naraki\Oauth\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface HasOauthScope
{
    public function scopeOauth(Builder $builder, $wheres = null);

}