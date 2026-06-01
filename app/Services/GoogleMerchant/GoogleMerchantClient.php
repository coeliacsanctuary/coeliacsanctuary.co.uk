<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Auth\Credentials\ServiceAccountCredentials;
use RuntimeException;

class GoogleMerchantClient
{
    public function __construct(
        protected bool $enabled,
        protected string $merchantId,
        protected string $serviceAccountKeyPath,
        protected string $dataSource,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function merchantId(): string
    {
        return $this->merchantId;
    }

    public function dataSource(): string
    {
        return $this->dataSource;
    }

    public function client(): ServiceAccountCredentials
    {
        $path = str_starts_with($this->serviceAccountKeyPath, '/') ? $this->serviceAccountKeyPath : base_path($this->serviceAccountKeyPath);

        if ( ! file_exists($path)) {
            throw new RuntimeException("Google Merchant service account key not found at [{$path}]");
        }

        /** @var string $contents */
        $contents = file_get_contents($path);

        return new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/content'],
            json_decode($contents, true),
        );
    }
}
