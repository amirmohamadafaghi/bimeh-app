<?php

declare(strict_types=1);

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'bale' => [
        'bot_token'      => env('BALE_BOT_TOKEN', 'mock'),
        'bot_username'   => env('BALE_BOT_USERNAME', 'afagh_bimeh_bot'),
        'webhook_secret' => env('BALE_WEBHOOK_SECRET', ''),
    ],

];