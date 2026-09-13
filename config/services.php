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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // SMS Gateway Configuration (placeholder)
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'), // log, twilio, nexmo, local_gateway
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
        'nexmo' => [
            'key' => env('NEXMO_KEY'),
            'secret' => env('NEXMO_SECRET'),
            'from' => env('NEXMO_FROM'),
        ],
        'local_gateway' => [
            'url' => env('SMS_GATEWAY_URL'),
            'username' => env('SMS_GATEWAY_USERNAME'),
            'password' => env('SMS_GATEWAY_PASSWORD'),
            'sender_id' => env('SMS_GATEWAY_SENDER_ID', 'MADANI'),
        ],
    ],

    // Web3Forms Configuration (untuk form aduan & saran internal)
    'web3forms' => [
        'access_key' => env('WEB3FORMS_ACCESS_KEY'),
        'email' => env('WEB3FORMS_EMAIL'),
    ],

];
