<?php

declare(strict_types=1);

return [
    'enabled' => env('GOOGLE_MERCHANT_ENABLED', false),

    'merchant_id' => env('GOOGLE_MERCHANT_ID'),

    'service_account_key_path' => env('GOOGLE_MERCHANT_SERVICE_ACCOUNT_KEY_PATH'),

    'data_source' => env('GOOGLE_MERCHANT_DATA_SOURCE'),
];
