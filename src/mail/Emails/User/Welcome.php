<?php namespace Naraki\Mail\Emails\User;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Welcome extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'mail::welcome';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('mail::tr.welcome.title'),
            'subject' => trans('mail::tr.welcome.subject', ['app_name' => config('app.name')]),
            'activation_token' => $this->data->activation_token
        ]);
    }

}
