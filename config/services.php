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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'tripjack' => [
        'api_key' => env('TRIPJACK_API_KEY'),
        // Must be explicitly set to "production" in .env to hit TripJack's live
        // hosts — defaults to "test" so a forgotten/unset env var can never
        // accidentally start charging against production inventory. Per-URL
        // env vars (TRIPJACK_HMS_BASE_URL etc.) still override this if set,
        // for local overrides that don't fit the test/production split.
        'env' => env('TRIPJACK_ENV', 'test'),
        'hms_base_url' => env('TRIPJACK_HMS_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
            ? 'https://hmssearch.tripjack.com/hms/v3'
            : 'https://apitest-hms.tripjack.com/hms/v3'),
        'booker_base_url' => env('TRIPJACK_BOOKER_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
            ? 'https://hmsbooker.tripjack.com/oms/v3'
            : 'https://apitest-hotel-booker.tripjack.com/oms/v3'),
        'booker_v1_base_url' => env('TRIPJACK_BOOKER_V1_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
            ? 'https://hmsbooker.tripjack.com/oms/v1'
            : 'https://apitest-hotel-booker.tripjack.com/oms/v1'),
        'nationality_base_url' => env('TRIPJACK_NATIONALITY_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
            ? 'https://hmssearch.tripjack.com/hms/v3'
            : 'https://apitest-hms.tripjack.com/hms/v3'),
        // 60s (not 30s) — a domestic Multi-City search (2-6 legs) can take
        // ~30s on its own against TripJack's sandbox; a shorter timeout was
        // causing spurious TripJackTimeoutExceptions on real, successful
        // searches. Shared with the hotel client too (same config key).
        'timeout' => env('TRIPJACK_TIMEOUT', 60),
        'connect_timeout' => env('TRIPJACK_CONNECT_TIMEOUT', 5),
        'retry_times' => env('TRIPJACK_RETRY_TIMES', 3),
        'retry_sleep_ms' => env('TRIPJACK_RETRY_SLEEP_MS', 200),

        // Flights API v2.0 — separate base host structure from Hotels
        // (apitest.tripjack.com / tripjack.com, no hms-/booker- prefixes),
        // same apikey header + TRIPJACK_ENV switch.
        'flight' => [
            'fms_base_url' => env('TRIPJACK_FLIGHT_FMS_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
                ? 'https://tripjack.com/fms/v1'
                : 'https://apitest.tripjack.com/fms/v1'),
            'oms_base_url' => env('TRIPJACK_FLIGHT_OMS_BASE_URL', env('TRIPJACK_ENV', 'test') === 'production'
                ? 'https://tripjack.com/oms/v1'
                : 'https://apitest.tripjack.com/oms/v1'),
            // tripjack:health-check alerts admins when the TripJack account's
            // totalBalance (User Detail API) drops below this, in INR.
            'low_balance_alert' => (float) env('TRIPJACK_LOW_BALANCE_ALERT', 25000),
        ],
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

];
