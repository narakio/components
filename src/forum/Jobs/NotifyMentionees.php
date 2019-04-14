<?php namespace Naraki\Forum\Jobs;

use Naraki\Core\Job;
use Naraki\Sentry\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Redis;
use Naraki\Forum\Emails\Mention;
use Naraki\Forum\Events\PostCreated;
use Naraki\Forum\Facades\Forum;
use Naraki\Mail\Jobs\SendMail;
use Naraki\System\Facades\System;

class NotifyMentionees extends Job
{
    /**
     * @var PostCreated
     */
    private $event;

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
            $this->processMentions();
        } catch (\Exception $e) {
            \Log::critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
//            app('bugsnag')->notifyException($e, ['mailData'=>$this->email->getData()], "error");
        }
        $this->delete();
    }

    private function processMentions()
    {
        $userModel = new User();
        $mentionedUsernames = $this->event->request->getMentions();
        $userDb = $userModel->newQuery()->select([$userModel->getQualifiedKeyName(), 'username', 'email', 'full_name'])
            ->whereIn('username', $mentionedUsernames)->get();
        $usersInConversationDb = Forum::post()->getUserPostTreeBySlug($this->event->commentData->forum_post_slug);

        $users = [];
        foreach ($userDb as $user) {
            $users[$user->getAttribute('username')] = $user;
        }
        $usersInConversation = [];
        foreach ($usersInConversationDb as $user) {
            $usersInConversation[$user->username] = true;
        }

        foreach ($mentionedUsernames as $user) {
            $notifiedUserKey = $users[$user]->getKey();
            $userNotifOptions = System::subscriptions()->cacheFrontendSubscriptions($notifiedUserKey);

            //The user has to have set notification preferences
            //Also we don't need to notify the person who's posting if the person includes a mention to itself.
            if (
                is_array($userNotifOptions) && isset($userNotifOptions['mention'])
                && $notifiedUserKey != $this->event->user->getKey()
            ) {
                if ($userNotifOptions['mention'] == true) {
                    $commentPosterInfo = $userModel->newQuery()
                        ->select(['username', 'full_name'])
                        ->where('users.user_id', $this->event->commentData->post_user_id)
                        ->first();
                    if (isset($userNotifOptions['reply']) && $userNotifOptions['reply'] == true) {
                        if (isset($usersInConversation[$users[$user]->username])) {
                            //If the user is mentioned and he's also subscribed to replies, he'll be notified twice:
                            //once for being mentioned, another for having subscribed to new replies
                            //the above tests avoid this double notification.
                            //In this case, only the reply notification will be sent.
                            return;
                        }
                    }
                    app(Dispatcher::class)
                        ->dispatch(new SendMail(new Mention([
                            'user' => $users[$user],
                            'slug' => $this->event->entityData->slug,
                            'comment_slug' => $this->event->commentData->forum_post_slug,
                            'mention_user' => sprintf(
                                '%s (@%s)',
                                $commentPosterInfo->getAttribute('full_name'),
                                $commentPosterInfo->getAttribute('username')
                            ),
                            'post_title' => $this->event->entityData->title
                        ])));

                }
            }

        }
    }


}