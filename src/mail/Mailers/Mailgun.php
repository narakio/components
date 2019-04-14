<?php namespace Naraki\Mail\Mailers;

use Mailgun\Model\Message\SendResponse;
use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;
use Illuminate\Support\Collection;
use Mailgun\Mailgun as RealMailgun;
use Mailgun\Message\MessageBuilder;

class Mailgun implements Mailer
{
    /**
     * @var \Mailgun\Mailgun
     */
    private $mailgun;
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;
    /**
     * @var Message
     */
    private $message;
    /**
     * @var \Illuminate\Support\Collection
     */
    private $config;

    /**
     *
     * @param \Illuminate\Support\Collection $config
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function __construct(Collection $config)
    {
        $this->config = $config;
        $this->mailgun = static::make($config->get('private_key'));
        $this->view = app()->make('view');
    }

    /**
     * @param string $privateKey
     * @return \Mailgun\Mailgun
     */
    public static function make($privateKey): RealMailgun
    {
        return RealMailgun::create($privateKey);
    }

    /**
     * @param \Naraki\Mail\Emails\Email $email
     * @return \Mailgun\Model\Message\SendResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function send(Email $email): SendResponse
    {
        $mailer = new static($email->getConfig());
        $mailer->message = new Message(new MessageBuilder(), $mailer->config);
        call_user_func([$email, 'fillMessage'], $mailer->message);
        $mailer->renderBody($email->getViewName(), $email->getViewData());

        return $mailer->mailgun->messages()->send(
            $email->config('domain'),
            $mailer->message->getMessage()
        );
    }

    /**
     *
     * @param string|array $view
     * @param array $data
     * @return void
     */
    private function renderBody($view, array $data)
    {
        $data['message'] = $this->message;
        $this->message->builder()->setHtmlBody(
            $this->view->make($view, $data)->render()
        );
    }

}