<?php namespace Naraki\Sentry\Models;

use Illuminate\Database\Eloquent\Model;

class PersonMeta extends Model
{

    public $table = 'people_meta';
    public $primaryKey = 'person_meta_id';

    protected $fillable = [
        'person_id',
        'person_meta_url',
    ];

}