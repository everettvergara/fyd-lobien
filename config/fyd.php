<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FYD CMS Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('FYD_NAME', 'FYD CMS'),

    'admin' => [
        'prefix' => 'admin',
        'middleware' => ['web', 'admin'],
    ],

    'public' => [
        'theme' => 'fyd-default',
    ],

];
