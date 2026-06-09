<?php

declare(strict_types=1);

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

    'getAddress' => [
        'url' => env('GET_ADDRESS_URL', 'https://api.getaddress.io'),
        'key' => env('GET_ADDRESS_KEY'),
    ],

    'idealPostcodes' => [
        'url' => env('IDEAL_POSTCODES_URL', 'https://api.ideal-postcodes.co.uk/v1'),
        'key' => env('IDEAL_POSTCODES_KEY'),
    ],

    'google' => [
        'maps' => [
            'admin' => env('GOOGLE_MAP_ADMIN'),
            'static' => env('VITE_GOOGLE_MAPS_STATIC_KEY'),
        ],
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

];
