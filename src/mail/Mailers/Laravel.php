<?php namespace Naraki\Mail\Mailers;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Laravel implements Mailer
{

    /**
     * @param \Naraki\Mail\Emails\Email $email
     * @return void
     */
    public static function send(Email $email)
    {
        \Mail::send($email->getViewName(), $email->getViewData(), function ($message) use ($email) {
            return call_user_func([$email, 'fillMessage'], $message);
        });
    }

}