<?php namespace Naraki\Forum\Emails;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Reply extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'forum::email.reply';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('forum::tr.email.reply.title'),
            'subject' => trans('forum::tr.email.reply.subject', ['app_name' => config('app.name')])
        ]);
    }

}
