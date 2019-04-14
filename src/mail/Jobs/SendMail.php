<?php namespace Naraki\Mail\Jobs;

use Naraki\Core\Job;
use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Emails\Email;

class SendMail extends Job
{
    public $queue = 'mail';
    private $email;
    private $driver;

    /**
     * Create a new job instance.
     *
     * @param $email \Naraki\Mail\Emails\Email
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
        $this->driver = $email->getTaskedMailer();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();
        try {
            if ($this->driver === Mailer::DRIVER_SMTP) {
                \Naraki\Mail\Mailers\Laravel::send($this->email);
            } else {
                \Naraki\Mail\Mailers\Mailgun::send($this->email);
            }
            $this->delete();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
            $this->release(60);
        }
    }

    public function getEmail()
    {
        return $this->email;
    }
}