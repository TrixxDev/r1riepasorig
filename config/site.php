<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default site season
    |--------------------------------------------------------------------------
    |
    | 1 = summer (Vasara), 2 = winter (Ziema).
    | Runtime value is stored in cart_config.site_season and cached.
    | SEASON in .env is only a fallback before the DB row exists.
    |
    */

    'default_season' => (int) env('SEASON', 1),

];
