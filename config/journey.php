<?php

declare(strict_types=1);

return [
    'enabled' => env('JOURNEY_ENABLED', true),

    'dont-track' => [
        'static/map/{latlng}',
    ],

    'host' => env('JOURNEY_TRACKER'),

    'token' => env('JOURNEY_TRACKER_TOKEN'),
];
