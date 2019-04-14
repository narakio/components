<?php namespace Naraki\Mail\Jobs;

use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Naraki\Core\Job;
use Naraki\Core\Support\Requests\Sanitizer;
use Naraki\Mail\Emails\Admin\Newsletter;
use Naraki\Mail\Facades\NarakiMail;
use Naraki\Sentry\Events\UserSubscribedToNewsletter;
use Naraki\System\Facades\System;
use Naraki\System\Models\SystemEvent;

class SubscribeToNewsletter extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private $input;

    /**
     *
     * @param $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $input = Sanitizer::clean($this->input);
            $data = NarakiMail::subscriber()->addPersonToLists($input);
            if (is_array($data)) {
                event(new UserSubscribedToNewsletter($data));
                System::log()->log(
                    SystemEvent::NEWSLETTER_SUBSCRIPTION,
                    $input
                );
            }
            $emailNotificationSubscribers = System::subscriptions()
                ->getSubscribedUsers(SystemEvent::NEWSLETTER_SUBSCRIPTION);
            if (!$emailNotificationSubscribers->isEmpty()) {
                foreach ($emailNotificationSubscribers as $sub) {
                    app(Dispatcher::class)->dispatch(new SendMail(new Newsletter([
                        'newsletter_email' => $input['email'] ?? null,
                        'newsletter_name' => $input['full_name'] ?? null,
                        'user' => $sub,
                    ])));
                }

            }

        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        $this->delete();
    }
}
