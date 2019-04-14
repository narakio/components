<?php namespace Naraki\Sentry\Jobs;

use Naraki\Sentry\Models\StatUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Naraki\Core\Job;

class UpdateOnUserLogin extends Job
{
    use InteractsWithQueue, SerializesModels;

//    public $queue = 'db';
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    private $user;
    /**
     * @var string
     */
    private $guard;
    /**
     * @var boolean
     */
    private $remember;

    /**
     * Create a new job instance.
     *
     * @param string $guard
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param boolean $remember
     */
    public function __construct($guard, $user, $remember)
    {
        $this->user = $user;
        $this->guard = $guard;
        $this->remember = $remember;
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
            StatUser::query()->where('user_id',
                $this->user->getKey())
                ->update(['stat_user_last_visit' => \Carbon\Carbon::now()->toDateTimeString()]);
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['user'=>$this->user->toArray()], "error");
        }
        $this->delete();
    }

}