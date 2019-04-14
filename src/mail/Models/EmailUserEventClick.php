<?php namespace Naraki\Mail\Models;

use Illuminate\Database\Eloquent\Model;

class EmailUserEventClick extends Model
{
    protected $primaryKey = 'email_user_click_id';
    protected $fillable = [
        'email_user_event_id',
        'email_user_click_url',
        'email_user_click_entity',
        'entity_id',
    ];
    public $timestamps = false;
    

}