<?php return [
    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be listed as possible emails to send
    |
    */
    'aliases' =>
        [
            'welcome' => \Naraki\Mail\Emails\User\Welcome::class,
            'contact' => \Naraki\Mail\Emails\Frontend\Contact::class,
            'password_reset' => \Naraki\Mail\Emails\User\PasswordReset::class
        ]
];
