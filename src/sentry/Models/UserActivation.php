<?php namespace Naraki\Sentry\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivation extends Model
{
    public $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activation_token'
    ];

}