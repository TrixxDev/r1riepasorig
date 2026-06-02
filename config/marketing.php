<?php

return [

    'meta' => [
        'pixel_id' => env('FACEBOOK_PIXEL_ID'),
        'capi_access_token' => env('META_CAPI_ACCESS_TOKEN'),
        'capi_test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
    ],

    'google_ads' => [
        'conversion_id' => env('GOOGLE_ADS_CONVERSION_ID'),
        'conversion_label' => env('GOOGLE_ADS_CONVERSION_LABEL'),
        'booking_conversion_label' => env('GOOGLE_ADS_BOOKING_CONVERSION_LABEL'),

        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
        'api_developer_token' => env('GOOGLE_ADS_API_DEVELOPER_TOKEN'),
        'oauth_client_id' => env('GOOGLE_ADS_OAUTH_CLIENT_ID'),
        'oauth_client_secret' => env('GOOGLE_ADS_OAUTH_CLIENT_SECRET'),
        'oauth_refresh_token' => env('GOOGLE_ADS_OAUTH_REFRESH_TOKEN'),
        'purchase_conversion_action' => env('GOOGLE_ADS_PURCHASE_CONVERSION_ACTION'),
        'booking_conversion_action' => env('GOOGLE_ADS_BOOKING_CONVERSION_ACTION'),
    ],

    'product_feed' => [
        'token' => env('PRODUCT_FEED_TOKEN'),
        'batch_size' => (int) env('PRODUCT_FEED_BATCH', 5000),
    ],

    'comparison_feed' => [
        // Serve last generated XML without rebuild (seconds). ?refresh=1 forces regeneration.
        'cache_seconds' => (int) env('COMPARISON_FEED_CACHE_SECONDS', 3600),
    ],

];
