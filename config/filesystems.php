<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'media' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_MEDIA_BUCKET', 'prod-coeliac-media'),
            'visibility' => 'public',
            'options' => [
                'CacheControl' => 'max-age=315360000, no-transform, public',
            ],
        ],

        'product-addons' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_PRODUCT_ADDONS_BUCKET', 'prod-coeliac-product-addons'),
            'throw' => true,
            'visibility' => 'private',
        ],

        'images' => [
            'driver' => env('IMAGES_STORAGE_DRIVER', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_IMAGES_BUCKET', 'prod-coeliac-images'),
            'options' => [
                'CacheControl' => 'max-age=315360000, no-transform, public',
            ],
        ],

        'review-images' => [
            'driver' => env('IMAGES_STORAGE_DRIVER', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_REVIEW_IMAGES_BUCKET', 'prod-coeliac-review-images'),
            'options' => [
                'CacheControl' => 'max-age=315360000, no-transform, public',
            ],
            'throw' => true,
        ],

        'uploads' => [
            'driver' => env('IMAGES_STORAGE_DRIVER', 's3'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_FILE_UPLOADS_BUCKET', 'prod-coeliac-file-uploads'),
        ],

        'backup' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
            'bucket' => env('AWS_BACKUPS_BUCKET', 'prod-coeliac-backups'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
