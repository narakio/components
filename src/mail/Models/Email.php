<?php namespace Naraki\Mail\Models;

use Naraki\Core\Contracts\HasAnEntity;
use Naraki\Core\Traits\Models\HasAnEntity as HasAnEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Email extends Model implements HasAnEntity
{
    use HasAnEntityTrait;

    public static $entityID =  \Naraki\Core\Models\Entity::EMAILS;
    public $timestamps = false;
    protected $primaryKey = 'email_id';
    protected $fillable = [
        'email_recipient_type_id',
        'email_content',
        'email_content_sources',
        'email_schedule_id'
    ];

}