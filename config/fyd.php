<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FYD CMS Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('FYD_NAME', 'FYD CMS'),

    'registration_enabled' => env('FYD_REGISTRATION_ENABLED', true),

    'admin' => [
        'prefix' => 'admin',
        'middleware' => ['web'],
    ],

    'public' => [
        'theme' => 'fyd-default',
    ],

];
