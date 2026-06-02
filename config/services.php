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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'recaptcha' => [
        'sitekey' => env('RECAPTCHA_SITEKEY'),
        'secret' => env('RECAPTCHA_SECRET'),
    ],

    'schedulebull' => [
        'key' => env('SCHEDULEBULL_API_KEY'),
        'base_url' => env('SCHEDULEBULL_BASE_URL', 'https://app.schedulebull.com/api3.php'),
        'client_api_key' => env('SCHEDULEBULL_CLIENT_API_KEY'),
        'cache_ttl_seconds' => (int) env('SCHEDULEBULL_CACHE_TTL', 21600),
    ],

    'appointment' => [
        'ws_url' => env('APPOINTMENT_WS_URL', null),
        'socket_secret' => env('APPOINTMENT_SOCKET_SECRET', null),
        'notify_url' => env('APPOINTMENT_NOTIFY_URL', 'http://127.0.0.1:3001/appoApi'),
    ],

    'mobile' => [
        'api_token' => env('MOBILE_API_TOKEN', ''),
    ],

    /*
    | Starco RSC (big tyres sync). Override STARCO_* in .env for production.
    */
    'starco' => [
        'api_base' => env('STARCO_API_BASE', 'http://remote.starco.lv:8153/api.rsc/'),
        'login' => env('STARCO_LOGIN', '202562'),
        'password' => env('STARCO_PASSWORD', '3R64p1EJuOaYnwct0FQQ'),
    ],

    /*
    | Parallel WhatsApp (HTTP JSON API). TextMeBot URLs stay unchanged.
    | WHATSAPP_PARALLEL_TEST_MODE=true → all parallel messages go to test_to only.
    | WHATSAPP_PARALLEL_TEST_MODE=false → group JIDs from wpp_group_* below (also used for TextMeBot recipients in code).
    | JIDs live here so controllers/queues never pass office_id by mistake as a recipient.
    */
    'whatsapp_parallel' => [
        'enabled' => filter_var(env('WHATSAPP_PARALLEL_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'api_key' => env('WHATSAPP_PARALLEL_API_KEY'),
        'send_url' => env('WHATSAPP_PARALLEL_SEND_URL', 'http://165.245.220.104/api/v1/messages/send'),
        'test_mode' => filter_var(env('WHATSAPP_PARALLEL_TEST_MODE', true), FILTER_VALIDATE_BOOLEAN),
        'test_to' => env('WHATSAPP_PARALLEL_TEST_TO', '+37128344474'),
        'wpp_group_urs' => env('WHATSAPP_WPP_JID_URS', '120363130984594947@g.us'),
        'wpp_group_krs' => env('WHATSAPP_WPP_JID_KRS', '120363150684433547@g.us'),
        'wpp_group_order' => env('WHATSAPP_WPP_JID_ORDER', '120363248805017034@g.us'),
        // Urs/Krs WhatsApp: ja false — jebkurā laikā (šodienas pieraksti); ja true — tikai 08:00–18:00 (RecordController).
        'restrict_office_hours' => filter_var(env('WPP_OFFICE_RESTRICT_BUSINESS_HOURS', true), FILTER_VALIDATE_BOOLEAN),
    ],

];
