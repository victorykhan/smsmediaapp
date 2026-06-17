<?php

return [
    'mastodon' => [
        'authorize_url' => 'https://{instance}/oauth/authorize',
        'token_url' => 'https://{instance}/oauth/token',
        'scopes' => ['read', 'write', 'follow'],
        'manual_fields' => ['instance' => 'Mastodon instance URL (e.g. mastodon.social)'],
    ],
    'x' => [
        'authorize_url' => 'https://twitter.com/i/oauth2/authorize',
        'token_url' => 'https://api.twitter.com/2/oauth2/token',
        'scopes' => ['tweet.read', 'tweet.write', 'users.read', 'offline.access'],
        'manual_fields' => [
            'api_key' => 'X API Key',
            'api_secret' => 'X API Secret',
            'bearer_token' => 'X Bearer Token',
        ],
    ],
    'instagram' => [
        'authorize_url' => 'https://www.facebook.com/v22.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v22.0/oauth/access_token',
        'scopes' => ['instagram_basic', 'instagram_content_publish', 'pages_show_list'],
        'manual_fields' => [
            'page_id' => 'Facebook Page ID',
            'ig_user_id' => 'Instagram Business Account ID',
        ],
    ],
    'threads' => [
        'authorize_url' => 'https://www.threads.net/oauth/authorize',
        'token_url' => 'https://graph.threads.net/v1.0/oauth/access_token',
        'scopes' => ['threads_basic', 'threads_content_publish'],
        'manual_fields' => ['user_id' => 'Threads User ID'],
    ],
    'facebook' => [
        'authorize_url' => 'https://www.facebook.com/v22.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v22.0/oauth/access_token',
        'scopes' => ['pages_manage_posts', 'pages_read_engagement', 'business_management'],
        'manual_fields' => [
            'page_id' => 'Facebook Page ID',
            'page_access_token' => 'Page Access Token',
        ],
    ],
    'youtube' => [
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'scopes' => ['https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube'],
        'manual_fields' => ['channel_id' => 'YouTube Channel ID'],
    ],
    'tiktok' => [
        'authorize_url' => 'https://www.tiktok.com/v2/auth/authorize',
        'token_url' => 'https://open.tiktokapis.com/v2/oauth/token/',
        'scopes' => ['user.info.basic', 'video.publish', 'video.upload'],
        'manual_fields' => ['open_id' => 'TikTok Open ID'],
    ],

    'pinterest' => [
        'authorize_url' => 'https://www.pinterest.com/oauth/',
        'token_url' => 'https://api.pinterest.com/v5/oauth/token',
        'scopes' => ['boards:read', 'pins:read', 'pins:write'],
        'manual_fields' => ['board_id' => 'Pinterest Board ID (from your board URL or developer tools)'],
    ],

    'linkedin_profile' => [
        'authorize_url' => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
        'scopes' => ['openid', 'profile', 'email', 'w_member_social'],
        'manual_fields' => [],
    ],

    'linkedin_company' => [
        'authorize_url' => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
        'scopes' => ['openid', 'profile', 'email', 'w_organization_social'],
        'manual_fields' => ['organization_id' => 'LinkedIn Organization/Page ID (e.g. 1234567 or urn:li:organization:1234567)'],
    ],
];
