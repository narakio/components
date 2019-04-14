<?php namespace Naraki\Core\Support\Viewable\Models;

use Illuminate\Database\Eloquent\Model;

class StatPageView extends Model
{
    protected $primaryKey = 'entity_type_id';

    public $fillable = [
        'entity_type_id',
        'cnt',
        'unq',
        'period_start',
        'period_end'
    ];

}