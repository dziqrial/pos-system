<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Paths
    |--------------------------------------------------------------------------
    |
    | Base path where all application modules reside.
    |
    */

    'path' => base_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Core Modules
    |--------------------------------------------------------------------------
    |
    | Modules that are always enabled regardless of store_modules settings.
    |
    */

    'core' => ['system', 'core'],

    /*
    |--------------------------------------------------------------------------
    | Optional Modules
    |--------------------------------------------------------------------------
    |
    | Modules that can be enabled/disabled per store via store_modules table.
    |
    */

    'optional' => ['loyalty', 'purchase_order', 'fnb', 'accounting'],

];
