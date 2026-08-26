<?php

declare(strict_types=1);

return [
    'enabled' => env('JOURNEY_TRACKER_ENABLED', true),

    'app-token' => env('JOURNEY_TRACKER_TOKEN'),

    'dont-track' => [
        'static/map/{latlng}',
        '/horizon/*',
        '/cs-adm/*',
        'fallback',
        '/nova*',
        '/ads.txt',
        '/blog/feed',
        '/feed',
        '/recipe/feed',
        '/sitemap.xml',
    ],

    'internal-event-endpoint' => 'api/event',

    'heartbeat-endpoint' => 'api/heartbeat',

    'queue' => env('JOURNEY_TRACKER_QUEUE', null),
];
