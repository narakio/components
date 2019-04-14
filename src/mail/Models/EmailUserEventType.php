<?php namespace Naraki\Mail\Models;

use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class EmailUserEventType extends Model
{
    use Enumerable;
    const OPENED = 1;
    const CLICKED = 2;
    const FAILED = 11;
    const COMPLAINED = 12;
    
    protected $primaryKey = 'email_user_event_type_id';
    protected $fillable = [
        'email_user_event_type_name'
    ];
    public $timestamps = false;


}