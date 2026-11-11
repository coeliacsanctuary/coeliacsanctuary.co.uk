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
        return "accounts/{$this->merchantId}/dataSources/{$this->dataSource}";
    }

    public function client(): ServiceAccountCredentials
    {
        return new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/content'],
            $this->serviceAccountKey(),
        );
    }

    /** @return array<string, mixed> */
    protected function serviceAccountKey(): array
    {
        $decoded = base64_decode($this->serviceAccountKeyPath, true);

        if ($decoded !== false && json_validate($decoded)) {
            return json_decode($decoded, true);
        }

        $path = str_starts_with($this->serviceAccountKeyPath, '/') ? $this->serviceAccountKeyPath : base_path($this->serviceAccountKeyPath);

        throw_unless(file_exists($path), new RuntimeException("Google Merchant service account key not found at [{$path}]"));

        /** @var string $contents */
        $contents = file_get_contents($path);

        return json_decode($contents, true);
    }
}
