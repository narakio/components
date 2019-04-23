<?php return [
    'email' => [
        'comment' => [
            'title' => 'A new comment was posted',
            'subject' => '[:app_name] A new comment was posted',
            'body1' => ':user commented in the ":post" post.',
        ],
        'reply' => [
            'title' => 'Someone replied to one of your comments',
            'subject' => '[:app_name] Someone replied to one of your comments',
            'body1' => ':user replied to one of your comments in the ":post" post.',
            'body2' => 'If you do not wish to receive such emails, notification settings can be modified by clicking on the cog icon in the comments section of a blog post.',
        ],
        'mention' => [
            'title' => 'Someone mentioned you in a comment',
            'subject' => '[:app_name] Someone mentioned you in a comment',
            'body1' => 'You were mentioned by :user in the ":post" post.',
            'body2' => 'If you do not wish to receive such emails, notification settings can be modified by clicking on the cog icon in the comments section of a blog post.',
            'cta' => 'Go to discussion'
        ],
    ],
    'comment_add_success' => 'Your comment has been submitted for publication! It will be posted in a moment.',
    'reply_add_success' => 'Your reply has been submitted for publication. It will be posted as soon as possible.',
    'comment_update_success' => 'Your comment has been submitted for update.',
    'posting_delay'=>'We enforce a two minute delay between posts. Please try again in a few moments.'
];
