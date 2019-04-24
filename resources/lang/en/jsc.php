<?php //Lang file containing translated strings for use in javascript code common to backend & frontend

return [
    'general' => [
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'next' => 'Next',
        'ok' => 'Ok',
        'prev' => 'Previous',
        'search' => 'Search',
    ],
    'pwd-strength' => [
        'too_short' => 'The password must meet the minimum length condition.',
        'too_long' => 'The password must fewer the maximum length condition.',
        'one_lowercase' => 'The password must contain at least one lowercase letter.',
        'one_uppercase' => 'The password must contain at least one uppercase letter.',
        'one_number' => 'The password must contain at least one number.',
        'special_char' => 'The password must contain at least one special character.',
        'has_repeats' => 'The password may not contain sequences of three or more repeated characters.'
    ],
    'avatar-uploader' => [
        'click_default' => 'Click on an avatar to make it your profile picture. Avatar changes will be saved automatically.',
        'avatar-tab' => 'Avatar',
        'avatar-ul-tab' => 'Upload avatar',
        'delete_avatar' => 'Delete avatar',
        'image_uploading' => 'Processing in progress...',
        'image_proceed' => 'Proceed to cropping',
        'image_uploaded' => 'The avatar has been processed, you can return to the avatar tab.',
        'image_url_copy' => 'Copy image url to clipboard',
    ],
    'dropzone' => [
        'choose_file' => 'Drag and drop your file here (or click to browse)',
        'max_size' => 'Maximum size:',
        'accepted_formats' => 'Accepted file formats: ',
        'file_too_big' => 'File is too big ({{filesize}} MB, maximum allowed: {{maxFilesize}} MB).',
        'file_too_big_laravel' => 'File is too big (:filesize MB, maximum allowed: :maxFilesize MB).',
        'invalid_type' => 'This file type is not allowed.',
        'response_error' => 'Server responded with code {{statusCode}}.',
        'cancel_upload' => 'Cancel upload',
        'cancel_confirm' => 'Confirm upload?',
        'remove_file' => '',
        'max_files_exceeded' => 'The maximum number of files was reached.',
        'delete_media' => 'Delete media',
        'edit_media' => 'Edit media',
        'units' => [
            'MB' => 'MB'
        ]
    ],
    'cropper' => [
        'resize_image' => 'Use handles to resize image',
        'preview' => 'Cropped preview',
        'crop_upload' => 'Crop & Upload',
        'crop' => 'Crop',
        'cancel' => 'Cancel',
        'reload' => 'Reset cropper to original state',
        'original_size' => 'Original size:'
    ],
    'wizard' => [
        'back' => 'Back',
        'next' => 'Next',
        'save' => 'Save'
    ],
    'modal' => [
        'error' => [
            'h' => 'Oops...',
            't' => 'Something went wrong! Please try again.'
        ],
        'token_expired' => [
            'h' => 'Session Expired!',
            't' => 'Please log in again to continue.',
        ],
        'unauthorized' => [
            'h' => 'Access Denied',
            't' => 'You are not authorized to view this page.',
        ],
    ],
];
