<?php namespace Naraki\Mail\Emails\Frontend;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Contact extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'mail::contact';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('email.contact.title'),
            'subject' => trans('email.contact.email_subject', ['app_name' => config('app.name')]),
        ]);
    }

}
