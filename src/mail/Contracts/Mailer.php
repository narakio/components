<?php namespace Naraki\Mail\Contracts;

use Naraki\Mail\Emails\Email;

interface Mailer
{
    const DRIVER_SMTP = 1;
    const DRIVER_MAILGUN = 2;

    public static function send(Email $email);

}