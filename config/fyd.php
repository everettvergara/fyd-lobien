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
        'middleware' => ['web'],
    ],

    'public' => [
        'theme' => 'fyd-default',
    ],

    'themes' => [
        'path' => base_path('themes'),
        'contrib_path' => base_path('contrib_themes'),
        'default' => 'fyd-default',
    ],

];
