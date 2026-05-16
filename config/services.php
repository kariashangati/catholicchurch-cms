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

    'wkhtmltopdf' => [
        'binary' => env('WKHTMLTOPDF_BINARY', 'C:/wkhtmltopdf/wkhtmltopdf.bat'),
    ],




    'mpesa' => [
    'env' => env('MPESA_ENV', 'sandbox'),
    'host' => env('MPESA_HOST', 'https://openapi.m-pesa.com'),
    'market' => env('MPESA_MARKET', 'vodacomTZN'),
    'country' => env('MPESA_COUNTRY', 'TZN'),
    'currency' => env('MPESA_CURRENCY', 'TZS'),
    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE'),
    'origin' => env('MPESA_ORIGIN', '*'),
    'api_key' => env('MPESA_API_KEY'),
    'public_key' => env('MPESA_PUBLIC_KEY'),
    'api_version' => env('MPESA_API_VERSION', '3.1'),
],

];