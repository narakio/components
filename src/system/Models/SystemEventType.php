<?php namespace Naraki\System\Models;

use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class SystemEventType extends Model
{
    use Enumerable;

    const BROADCAST = 1;
    const EMAIL = 2;

    public $timestamps = false;
    protected $primaryKey = 'system_event_type_id';

    protected $fillable = [
        'system_event_type_name'
    ];

}
