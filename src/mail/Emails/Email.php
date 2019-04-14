<?php namespace Naraki\Mail\Emails;

use Naraki\Mail\Contracts\Mailer;
use Naraki\Mail\Support\ViewData;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class Email
{
    use SerializesModels;

    /**
     * @var string
     */
    protected $viewName;
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $viewData = [];
    /**
     * @var \stdClass
     */
    protected $data;
    /**
     * @var array
     */
    protected $files;
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;
    /**
     * @var int
     */
    protected $taskedMailer = Mailer::DRIVER_MAILGUN;

    /**
     *
     * @param array $data
     * @param array $files
     */
    public function __construct(array $data, $files = null)
    {
        $this->parseFiles($files);
        $this->data = (object)$data;
        $this->config = new Collection([
            'from.address' => config('mail.from.address'),
            'from.name' => config('mail.from.name'),
            'test_mode' => config('mail.mailgun.test_mode'),
            'reply_to.address' => config('mail.from.address'),
            'reply_to.name' => config('mail.from.name'),
            'domain' => config('mail.mailgun.domain'),
            'timezone' => config('app.timezone'),
            'private_key' => config('mail.mailgun.keys.private')
        ]);
        $this->viewData = new ViewData($data);
        $this->prepareViewData();
    }

    /**
     * The default email is sent to the currently logged in user.
     * Child classes should have their own implementation
     *
     * @param \Illuminate\Mail\Message $message
     */
    public function fillMessage($message)
    {
        $message->subject($this->viewData->get('subject'))
            ->to($this->viewData->get('recipient_email'), $this->viewData->get('recipient_name'));
    }

    /**
     * @return void
     */
    public function prepareViewData()
    {
        $this->viewData->add([
            'recipient_email' => $this->data->user->email,
            'recipient_name' => $this->data->user->full_name
        ]);
    }

    /**
     * @param array $files
     * @return void
     */
    private function parseFiles($files)
    {
        if (!is_null($files)) {
            foreach ($files as $file) {
                $path = storage_path() . '/uploads/' . str_random(5) . $file->getClientOriginalName();
                $this->files[] = (object)[
                    'path' => $path,
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType()
                ];
                \File::move($file->getRealPath(), $path);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getViewName(): string
    {
        return $this->viewName;
    }

    /**
     * @return array
     */
    public function getViewData(): array
    {
        return $this->viewData->toArray();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function config($key)
    {
        return $this->config->get($key);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfig($key, $value)
    {
        $this->config->put($key, $value);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return int
     * @see \Naraki\Mail\Contracts\Mailer
     */
    public function getTaskedMailer(): int
    {
        return $this->taskedMailer;
    }

}