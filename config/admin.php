<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where the admin panel lives
    |--------------------------------------------------------------------------
    |
    | Set ADMIN_PATH in .env. Letters, numbers and dashes only, no slashes.
    | It has to be read through a config file rather than env() directly,
    | because once you run `php artisan config:cache` on the live server
    | every env() call outside config/ returns null — which would silently
    | put the panel back at /admin.
    |
    */

    'path' => trim((string) env('ADMIN_PATH', 'admin'), '/') ?: 'admin',

    /*
    |--------------------------------------------------------------------------
    | The first admin account
    |--------------------------------------------------------------------------
    |
    | Only read by `php artisan db:seed`, and only when no account with this
    | email exists yet. Kept in .env so no password is ever written in code.
    |
    */

    'name'     => env('ADMIN_NAME'),
    'email'    => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),

];
