<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Phone Configuration
    |--------------------------------------------------------------------------
    */
    'phone' => [
        'default_country_code' => env('COMMUNICATION_PHONE_DEFAULT_COUNTRY_CODE', '255'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Segment Rules
    |--------------------------------------------------------------------------
    */
    'segments' => [
        'gsm_single' => 160,
        'gsm_multi' => 153,
        'unicode_single' => 70,
        'unicode_multi' => 67,
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Providers
    |--------------------------------------------------------------------------
    */
'sms' => [
    'default_provider' => env('COMMUNICATION_DEFAULT_PROVIDER', 'beem'),

    'beem' => [
        'send_url' => env('BEEM_SMS_SEND_URL', 'https://apisms.beem.africa/v1/send'),
        'balance_url' => env('BEEM_SMS_BALANCE_URL', 'https://apisms.beem.africa/public/v1/vendors/balance'),

        'api_key' => env('BONGO_LIVE_KEY'),
        'secret_key' => env('BONGO_LIVE_SECRET'),
        'sender_id' => env('BONGO_SENDER_ID'),

        'timeout' => env('BEEM_SMS_TIMEOUT', 30),
        'connect_timeout' => env('BEEM_SMS_CONNECT_TIMEOUT', 10),
        'retry_times' => env('BEEM_SMS_RETRY_TIMES', 2),
        'retry_sleep_ms' => env('BEEM_SMS_RETRY_SLEEP_MS', 300),
        'verify_ssl' => env('BEEM_SMS_VERIFY_SSL', true),
    ],
],
];