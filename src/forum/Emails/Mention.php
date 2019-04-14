<?php namespace Naraki\Forum\Emails;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Mention extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'forum::email.mention';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('email.mention.title'),
            'subject' => trans('email.mention.subject', ['app_name' => config('app.name')])
        ]);
    }

}
