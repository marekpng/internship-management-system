<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | This option controls the authentication guard that will be used while
    | authenticating users. This should correspond with one of your guards
    | from the "auth" configuration file.
    |
    */

    'guard' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport will load encryption keys from the "storage" directory by default.
    | Do NOT put keys into .env for a team project unless you manage them properly.
    |
    | Leave these as null so Passport uses:
    |   storage/oauth-private.key
    |   storage/oauth-public.key
    |
    */

    'private_key' => null,
    'public_key'  => null,

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    */

    'connection' => env('PASSPORT_CONNECTION'),

];
