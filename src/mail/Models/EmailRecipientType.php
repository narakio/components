<?php namespace Naraki\Mail\Models;

use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class EmailRecipientType extends Model
{
    use Enumerable;

    protected $primaryKey = 'email_recipient_type_id';
    protected $fillable = ['email_recipient_type_name'];
    public $timestamps = false;

    const ALL = 1;
    const ADMIN = 2;
    public static $classMap = [
        self::ALL             => '\Naraki\Mail\Emails\Bulk',
        self::ADMIN           => '\Naraki\Mail\Emails\Bulk',
    ];

    /**
     * @param int $emailRecipientTypeID
     *
     * @return string
     */
    public static function getEmailClass($emailRecipientTypeID)
    {
        if (isset(self::$classMap[$emailRecipientTypeID])) {
            return self::$classMap[$emailRecipientTypeID];
        }

        return self::$classMap[self::ALL];

    }

}
