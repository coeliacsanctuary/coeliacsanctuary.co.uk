<?php

declare(strict_types=1);

return [
    /*
     *  You'll find both the API token and endpoint on Mailcoach'
     *  API tokens screen in the Mailcoach settings.
     */
    'api_token' => env('MAILCOACH_API_TOKEN'),

    'incoming_key' => env('MAILCOACH_INCOMING_KEY'),

    'endpoint' => env('MAILCOACH_API_ENDPOINT'),

    'newsletter_id' => env('MAILCOACH_NEWSLETTER_ID'),
];
