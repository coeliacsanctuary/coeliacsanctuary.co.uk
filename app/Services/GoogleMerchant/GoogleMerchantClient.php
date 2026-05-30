<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Client;
use RuntimeException;

class GoogleMerchantClient
{
    public function __construct(
        protected bool $enabled,
        protected string $merchantId,
        protected string $serviceAccountKeyPath,
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

    public function client(): Client
    {
        if ( ! file_exists($this->serviceAccountKeyPath)) {
            throw new RuntimeException("Google Merchant service account key not found at [{$this->serviceAccountKeyPath}]");
        }

        $client = new Client();
        $client->setAuthConfig($this->serviceAccountKeyPath);
        $client->setScopes(['https://www.googleapis.com/auth/content']);

        return $client;
    }
}
