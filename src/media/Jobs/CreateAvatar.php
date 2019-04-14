<?php namespace Naraki\Media\Jobs;

use Naraki\Core\Job;
use Naraki\Media\Facades\Media as MediaProvider;

class CreateAvatar extends Job
{
    public $queue = 'db';

    private $username;
    private $filename;

    /**
     *
     * @param $username
     * @param $filename
     */
    public function __construct($username, $filename)
    {
        $this->username = $username;
        $this->filename = $filename;
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
            MediaProvider::image()->createAvatar($this->username, $this->filename ?: $this->username);
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
        }
        $this->delete();
    }


}