<?php return [
    'auth' => [
        'failed' => 'Either these credentials do not match our records, or your account hasn\'t been activated yet.',
        'failed_not_allowed' => 'Your account is not permitted to access the backoffice. Please contact your system administrator.',
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
        'alerts' => [
            'registered_title' => 'Registration complete',
            'registered_body' => 'Thank you! We\'ve sent you an e-mail to proceed with the activation of your account.',
            'activated_title' => 'Account activation complete',
            'activated_body' => 'Great! We have activated your account, please log in!',
            'activation_error_title' => 'Account activation error',
            'activation_error_body' => 'It looks like the activation link isn\'t valid anymore. Has the account not been activated already? Try logging in.',
            'account_deleted_title' => 'Account deleted',
            'account_deleted_body' => 'Your account was deleted.',
            'email_title' => 'Resetting your password',
            'email_body' => 'Please provide your e-mail below. We\'ll send you a link by e-mail allowing you to set a new password.',
            'reset_title' => 'Your password has been reset!',
            'reset_body' => 'You can login using your new password.',
            'recaptcha_title' => 'Oops, something went wrong!',
            'recaptcha_body' => 'We were unable to validate your request through Google Recaptcha. Please try submitting the form again in a few moments.',
            'email_reset_title' => 'Almost done!',
            'email_reset_body' => 'Please enter the password of your choice below and we\'ll reset it for you.'
        ],
        'content' => [
            'email' => 'E-Mail Address',
            'send_link' => 'Send Password Reset Link'
        ],
        'create_account' => 'Create an account',
        'login_account' => 'Log in to your account',
        'register_username_help' => 'Can contains letters, numbers and underscores. Must contain between 5 and 25 characters.',
        'email_address' => 'E-Mail Address',
        'password' => 'Password',
        'login' => 'Log In',
        'register_account' => 'Register an account',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot Your Password?',
        'password_help' => 'Must have a minimum of 8 characters.',
        'hide_password' => 'Hide password',
        'show_password' => 'Show password',
        'required_fields' => 'Fields marked with an asterisk (*) are mandatory.',
        'register' => 'Register'
    ],
    'routes' => [
        'home' => '/',
        'admin_login' => 'login',
        'login' => 'login',
        'activate' => 'register/activate/{token}',
        'register' => 'register',
        'password_reset' => 'password/reset',
        'password_email' => 'password/email',
        'password_reset_token' => 'password/reset/{token}/{email}',
        'settings_profile' => 'settings/profile',
        'settings_notifications' => 'settings/notifications',
        'settings_account' => 'settings/account',
        'contact' => 'contact',
        'user' => 'user/{slug}',
        'search' => 'search/{q?}',
        'privacy' => 'privacy',
        'terms_service' => 'terms-of-service'
    ],
    'jsonld' => [
        'organizations' => [
            'Airline' => 'Airline',
            'AnimalShelter' => 'Animal shelter',
            'AutomotiveBusiness' => 'Automotive business',
            'ChildCare' => 'Child care',
            'Corporation' => 'Corporation',
            'Dentist' => 'Dentist',
            'DryCleaningOrLaundry' => 'Dry cleaning or laundry',
            'EducationalOrganization' => 'Educational organization',
            'EmergencyService' => 'Emergency service',
            'EmploymentAgency' => 'Employment agency',
            'EntertainmentBusiness' => 'Entertainment business',
            'FinancialService' => 'Financial service',
            'FoodEstablishment' => 'Food establishment',
            'GovernmentOffice' => 'Government office',
            'GovernmentOrganization' => 'Government organization',
            'HealthAndBeautyBusiness' => 'Health and beauty business',
            'HomeAndConstructionBusiness' => 'Home and construction business',
            'InternetCafe' => 'Internet cafe',
            'LegalService' => 'Legal service',
            'Library' => 'Library',
            'LocalBusiness' => 'Local business',
            'LodgingBusiness' => 'Lodging business',
            'MedicalOrganization' => 'Medical organization',
            'NewsMediaOrganization' => 'News media organization',
            'NGO' => 'NGO',
            'Organization' => 'Organization',
            'PerformingGroup' => 'Performing group',
            'ProfessionalService' => 'Professional service',
            'RadioStation' => 'Radio station',
            'RealEstateAgent' => 'Real estate agent',
            'RecyclingCenter' => 'Recycling center',
            'SelfStorage' => 'Self storage',
            'ShoppingCenter' => 'Shopping center',
            'SportsActivityLocation' => 'Sports activity location',
            'SportsOrganization' => 'Sports organization',
            'Store' => 'Store',
            'TelevisionStation' => 'Television station',
            'TouristInformationCenter' => 'Tourist information center',
            'TravelAgency' => 'Travel agency',
        ],
        'websites' => [
            'WebSite' => 'Website',
            'Blog' => 'Blog'
        ]
    ]
];
