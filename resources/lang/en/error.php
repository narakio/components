<?php

return [
    'http' => [
        '401' => 'Non-authenticated users may not proceed.',
        '500' => [
            'general_error' => 'The operation caused an error. We\'ll be tracking the source of the problem shortly.',
            'general_retrieval_error' => 'The requested resource could not be retrieved. It may have been deleted.',
            'user_not_found' => 'We could not find a user by that username.',
            'group_not_found' => 'We could not find a group by that slug.'
        ],
        '422' => [
            'oauth_email_unverif' => 'The email address used to log in is not verified, therefore we cannot create an account 
          on this platform. We require a verified email address to get in touch with you if needed.'
        ],
        '403' => "Access to this resource is forbidden.",
        '404' => "Looks like we weren't able to locate that resource. Our apologies.",
        '405' => "The platform cannot process a request with that method.",
        '419' => "Your session has expired. Please log back in or refresh the page.",
        '429' => "We are receiving too many requests at the moment. Please try again in a few moments.",
        '503' => "Service unavailable. We are doing some cleanup.",
        'oauth_email' => 'The email associated with the account you used to log in already exists in our database.'
    ],
    'form' => [
        'identical_passwords' => 'The password you entered is identical to your previous password! Please enter a different password.',
        'wrong_password' => 'The current password you entered does not match what we have on record.',
    ],
];