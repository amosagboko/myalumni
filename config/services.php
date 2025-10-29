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
            // Category-specific service codes
            // Format: 'fee_type_code' => ['category_slug' => 'service_code']
            // For Postgraduate, we support qualification-specific codes: 'postgraduate-phd', 'postgraduate-msc', 'postgraduate-pgd'
            'registration' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                'postgraduate-phd' => '003486REG2025_PHD',
                'postgraduate-msc' => '003486REG2025_MSC',
                'postgraduate-pgd' => '003486REG2025_PGD',
                'undergraduate-full-time' => '003486REG2025_UGFT',
                'undergraduate-part-time' => '003486REG2025_UGPT',
                'diploma' => '003486REG2025_DIP',
            ],
            'development_levy' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                'postgraduate-phd' => '003486DEV2025_PHD',
                'postgraduate-msc' => '003486DEV2025_MSC',
                'postgraduate-pgd' => '003486DEV2025_PGD',
                'undergraduate-full-time' => '003486DEV2025_UGFT',
                'undergraduate-part-time' => '003486DEV2025_UGPT',
                'diploma' => '003486DEV2025_DIP',
            ],
            'data_processing' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                'postgraduate-phd' => '003486DAT2025_PHD',
                'postgraduate-msc' => '003486DAT2025_MSC',
                'postgraduate-pgd' => '003486DAT2025_PGD',
                'undergraduate-full-time' => '003486DAT2025_UGFT',
                'undergraduate-part-time' => '003486DAT2025_UGPT',
                'diploma' => '003486DAT2025_DIP',
            ],
            'tech_support' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                'postgraduate-phd' => '003486TEC2025_PHD',
                'postgraduate-msc' => '003486TEC2025_MSC',
                'postgraduate-pgd' => '003486TEC2025_PGD',
                'undergraduate-full-time' => '003486TEC2025_UGFT',
                'undergraduate-part-time' => '003486TEC2025_UGPT',
                'diploma' => '003486TEC2025_DIP',
            ],
            // Subscription - Add category-specific codes as needed (STRICT - NO FALLBACKS)
            'subscription' => [
                // Add category-specific codes here if needed
            ],
            // EOI (Expression of Interest) fees (same for all)
            'eoi-nat-president' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-deputy-national' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-sec-gen' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-deputy-nat-sec-gen' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-treasurer' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-fin-sec' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-pro' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-org-sec' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-wel-off' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-nat-provost' => [
                'default' => '0034865MAOAN',
            ],
            'eoi-sudo' => [
                'default' => '0034865MAOAN',
            ],

            // Add more fee types as needed
        ],
    ],

];
