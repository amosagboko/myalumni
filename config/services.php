<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'credocentral' => [
        'base_url' => env('CREDOCENTRAL_API_URL', 'https://api.credocentral.com'),
        'public_key' => env('CREDOCENTRAL_PUBLIC_KEY'),
        'secret_key' => env('CREDOCENTRAL_SECRET_KEY'),
        'test_mode' => env('CREDOCENTRAL_TEST_MODE', false),
        'service_codes' => [
            'subscription-fee-all' => '003486U9Q446',
            'eoi-nat-president'        => '0034865MAOAN',
            'eoi-deputy-national'      => '0034865MAOAN',
            'eoi-nat-sec-gen'          => '0034865MAOAN',
            'eoi-deputy-nat-sec-gen'   => '0034865MAOAN',
            'eoi-nat-treasurer'        => '0034865MAOAN',
            'eoi-nat-fin-sec'          => '0034865MAOAN',
            'eoi-nat-pro'              => '0034865MAOAN',
            'eoi-nat-org-sec'          => '0034865MAOAN',
            'eoi-nat-wel-off'          => '0034865MAOAN',
            'eoi-nat-provost'          => '0034865MAOAN',
            'eoi-sudo'                 => '0034865MAOAN',
            'subscription'             => '0034865MAOAN',


            // Add more as needed
        ],
    ],

];
