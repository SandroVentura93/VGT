<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'admin_username' => env('ADMIN_USERNAME', 'admin'),
    'admin_password' => env('ADMIN_PASSWORD'),

    'interbank_transfer' => [
        'bank' => 'Interbank',
        'currency' => 'Soles',
        'account_type' => 'Cuenta Simple Soles',
        'account_number' => '898 3364503905',
        'cci' => '00389801336450390547',
        'account_holder' => 'Sandro E. Ventura Mendoza',
    ],

    'social' => [
        'phone_label' => env('STORE_PUBLIC_PHONE_LABEL', '+51 967 151 428'),
        'phone_uri' => env('STORE_PUBLIC_PHONE_URI', '+51967151428'),
        'whatsapp_url' => env('SOCIAL_WHATSAPP_URL', 'https://wa.me/51967151428?text=Hola%20Ventura%20Global%20Technology%2C%20quiero%20hacer%20una%20consulta'),
        'instagram_url' => env('SOCIAL_INSTAGRAM_URL', ''),
        'facebook_url' => env('SOCIAL_FACEBOOK_URL', ''),
        'tiktok_url' => env('SOCIAL_TIKTOK_URL', ''),
        'youtube_url' => env('SOCIAL_YOUTUBE_URL', ''),
        'linkedin_url' => env('SOCIAL_LINKEDIN_URL', ''),
        'x_url' => env('SOCIAL_X_URL', ''),
    ],

];
