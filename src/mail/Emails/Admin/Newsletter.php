<?php namespace Naraki\Mail\Emails\Admin;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Newsletter extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'mail::newsletter';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('mail::tr.newsletter.title'),
            'subject' => trans('mail::tr.newsletter.email_subject', ['app_name' => config('app.name')]),
        ]);
    }

}
