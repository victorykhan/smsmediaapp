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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // Social media OAuth credentials
    'mastodon' => [
        'client_id' => env('MASTODON_CLIENT_ID'),
        'client_secret' => env('MASTODON_CLIENT_SECRET'),
        'redirect' => '/oauth/mastodon/callback',
    ],
    'x' => [
        'client_id' => env('X_CLIENT_ID'),
        'client_secret' => env('X_CLIENT_SECRET'),
        'redirect' => '/oauth/x/callback',
    ],
    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => '/oauth/instagram/callback',
    ],
    'threads' => [
        'client_id' => env('THREADS_CLIENT_ID'),
        'client_secret' => env('THREADS_CLIENT_SECRET'),
        'redirect' => '/oauth/threads/callback',
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => '/oauth/facebook/callback',
    ],
    'youtube' => [
        'client_id' => env('YOUTUBE_CLIENT_ID'),
        'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
        'redirect' => '/oauth/youtube/callback',
    ],
    'tiktok' => [
        'client_id' => env('TIKTOK_CLIENT_ID'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect' => '/oauth/tiktok/callback',
    ],

    'pinterest' => [
        'client_id' => env('PIN_CLIENT_ID'),
        'client_secret' => env('PIN_CLIENT_SECRET'),
        'redirect' => '/oauth/pinterest/callback',
    ],

    'linkedin_profile' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => '/oauth/linkedin_profile/callback',
    ],

    'linkedin_company' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => '/oauth/linkedin_company/callback',
    ],
];
