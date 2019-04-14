<?php namespace Naraki\Forum\Jobs;

use Illuminate\Bus\Dispatcher;
use Naraki\Core\Job;
use Naraki\Forum\Emails\Comment;
use Naraki\Forum\Emails\Reply;
use Naraki\Forum\Events\PostCreated;
use Naraki\Forum\Facades\Forum;
use Naraki\Mail\Jobs\SendMail;
use Naraki\Sentry\Models\User;
use Naraki\System\Facades\System;
use Naraki\System\Models\SystemEvent;

class NotifyReplySubscribers extends Job
{
    /**
     * @var PostCreated
     */
    private $event;
    /**
     * @var \Naraki\Sentry\Models\User
     */
    private $commentPosterInfo;

    public function __construct(PostCreated $event)
    {
        $this->event = $event;
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
            $this->commentPosterInfo = (new User())->newQuery()
                ->select(['username', 'full_name'])
                ->where('users.user_id', $this->event->commentData->post_user_id)
                ->first();
            $this->processUsers();
            $this->sendToBackendSubscribers();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
        }
        $this->delete();
    }

    private function processUsers()
    {
        $usersInConversation = Forum::post()->getUserPostTreeBySlug($this->event->commentData->forum_post_slug);

        foreach ($usersInConversation as $user) {
            if ($user->username != $this->event->user->getAttribute('username')) {
                $userNotifOptions = System::subscriptions()->cacheFrontendSubscriptions($user->user_id);

                //The user has to have set notification preferences
                //Also we don't need to notify the person who's posting if the person replies to themselves.
                if (is_array($userNotifOptions) && isset($userNotifOptions['blog_post_mention'])) {
                    if ($userNotifOptions['blog_post_mention']) {
                        app(Dispatcher::class)
                            ->dispatch(new SendMail(new Reply([
                                'user' => $user,
                                'slug' => $this->event->entityData->slug,
                                'comment_slug' => $this->event->commentData->forum_post_slug,
                                'reply_user' => sprintf(
                                    '%s (@%s)',
                                    $this->commentPosterInfo->getAttribute('full_name'),
                                    $this->commentPosterInfo->getAttribute('username')
                                ),
                                'post_title' => $this->event->entityData->title
                            ])));

                    }
                }
            }
        }
    }

    private function sendToBackendSubscribers()
    {
        $emailNotificationSubscribers = System::subscriptions()
            ->getSubscribedUsers(SystemEvent::BLOG_POST_COMMENT);
        if (!$emailNotificationSubscribers->isEmpty()) {
            foreach ($emailNotificationSubscribers as $sub) {
                app(Dispatcher::class)
                    ->dispatch(new SendMail(new Comment([
                        'user' => $sub,
                        'slug' => $this->event->entityData->slug,
                        'comment_slug' => $this->event->commentData->forum_post_slug,
                        'reply_user' => sprintf(
                            '%s (@%s)',
                            $this->commentPosterInfo->getAttribute('full_name'),
                            $this->commentPosterInfo->getAttribute('username')
                        ),
                        'post_title' => $this->event->entityData->title
                    ])));
            }
        }
        
    }


}