<?php

return [
    'email' => [
        'comment' => [
            'title' => '__A new comment was posted',
            'subject' => '__[:app_name] A new comment was posted',
            'body1' => '__:user commented in the ":post" post.',
        ],
        'reply' => [
            'title' => '__Someone replied to one of your comments',
            'subject' => '__[:app_name] Someone replied to one of your comments',
            'body1' => '__:user replied to one of your comments in the ":post" post.',
            'body2' => '__If you do not wish to receive such emails, notification settings can be modified by clicking on the cog icon in the comments section of a blog post.',
        ],
        'mention' => [
            'title' => '__Someone mentioned you in a comment',
            'subject' => '__[:app_name] Someone mentioned you in a comment',
            'body1' => '__You were mentioned by :user in the ":post" post.',
            'body2' => '__If you do not wish to receive such emails, notification settings can be modified by clicking on the cog icon in the comments section of a blog post.',
            'cta' => '__Go to discussion',
        ],
    ],
    'comment_add_success' => '__Your comment has been submitted for publication! It will be posted in a moment.',
    'reply_add_success' => '__Your reply has been submitted for publication. It will be posted as soon as possible.',
    'comment_update_success' => '__Your comment has been submitted for update.',
    'posting_delay' => '__We enforce a two minute delay between posts. Please try again in a few moments.',

];