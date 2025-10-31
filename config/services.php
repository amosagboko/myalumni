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
            // IMPORTANT: Replace the placeholder values below with ACTUAL Credo Central service codes
            // The KEY is the category slug (e.g., 'undergraduate-full-time')
            // The VALUE is whatever service code Credo Central provides (can be any format)
            // For Postgraduate, we support qualification-specific codes: 'postgraduate-phd', 'postgraduate-msc', 'postgraduate-pgd'
            'registration' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                // Replace these placeholder values with actual Credo Central codes
                'postgraduate-phd' => '003486UHBXXG',
                'postgraduate-msc' => '003486A4A30C',
                'postgraduate-pgd' => '0034866M79QZ',
                // Undergraduate categories - replace with actual Credo Central codes
                'undergraduate-full-time' => '003486PYW8VS',
                'undergraduate-part-time' => '0034860PZGM7',
                'diploma' => '003486Q4TVWL',
            ],
            'development_levy' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                // Replace these placeholder values with actual Credo Central codes
                'postgraduate-phd' => '003486ZWDECG',
                'postgraduate-msc' => '003486CCZPVP',
                'postgraduate-pgd' => '003486T0R3FS',
                'undergraduate-full-time' => '003486M6S4XV',
                'undergraduate-part-time' => '003486M0T2MO',
                'diploma' => '003486LKJG80',
            ],
            'data_processing' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                // Replace these placeholder values with actual Credo Central codes
                'postgraduate-phd' => '0034865JCWIM',
                'postgraduate-msc' => '003486LALDE9',
                'postgraduate-pgd' => '00348631YJUP',
                'undergraduate-full-time' => '003486T26SAE',
                'undergraduate-part-time' => '003486MJKMC1',
                'diploma' => '00348665ECWG',
            ],
            'tech_support' => [
                // Postgraduate subcategories based on qualification type (STRICT - NO FALLBACKS)
                // Replace these placeholder values with actual Credo Central codes
                'postgraduate-phd' => '003486ITNQTE',
                'postgraduate-msc' => '0034867F12P6',
                'postgraduate-pgd' => '0034860KE8LF',
                'undergraduate-full-time' => '003486TJ3QEV',
                'undergraduate-part-time' => '0034862114FZ',
                'diploma' => '003486UA8M7C',
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
