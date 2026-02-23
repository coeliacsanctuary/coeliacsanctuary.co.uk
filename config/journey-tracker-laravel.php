<?php

return [
    'enabled' => env('JOURNEY_TRACKER_ENABLED', true),

    'app-token' => env('JOURNEY_TRACKER_TOKEN'),

    'dont-track' => [
        'static/map/{latlng}',
    ],

    'internal-event-endpoint' => 'api/event',

    'host' => env('JOURNEY_TRACKER_HOST', 'https://journey-tracker.cloud'),
];
