<?php namespace Naraki\Forum\Emails;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class Comment extends Email
{
    protected $taskedMailer = Mailer::DRIVER_SMTP;
    protected $viewName = 'forum::email.comment';

    public function prepareViewData()
    {
        parent::prepareViewData();
        $this->viewData->add([
            'title' => trans('forum::tr.email.comment.title'),
            'subject' => trans('forum::tr.email.comment.subject', ['app_name' => config('app.name')])
        ]);
    }

}
