<?php namespace Naraki\System\Models;

use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class SystemEvent extends Model
{
    use Enumerable;

    const NEWSLETTER_SUBSCRIPTION = 1;
    const CONTACT_FORM_MESSAGE = 2;
    const BLOG_POST_COMMENT = 3;
    const BLOG_POST_MENTION = 4;
    const BLOG_POST_REPLY = 5;

    public $timestamps = false;
    protected $primaryKey = 'system_event_id';

    protected $fillable = [
        'system_event_name'
    ];

}
