<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paysera API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for the Paysera payment gateway.
    |
    */

    'paysera' => [
        'project_id' => env('PAYSERA_PROJECT_ID', '209872'),
        'sign_password' => env('PAYSERA_SIGN_PASSWORD', 'ef3e86e4902558e3779ecc84d72a6d8c'),
        'currency' => env('PAYSERA_CURRENCY', 'EUR'),
        'country' => env('PAYSERA_COUNTRY', 'LV'),
        'test_mode' => env('PAYSERA_TEST_MODE', false),
    ],
]; 
