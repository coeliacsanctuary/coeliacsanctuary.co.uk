<?php

declare(strict_types=1);

namespace Tests\Unit\Services\GoogleMerchant;

use App\Services\GoogleMerchant\GoogleMerchantClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GoogleMerchantClientTest extends TestCase
{
    protected function makeClient(bool $enabled = true, string $merchantId = '12345', string $keyPath = '', string $dataSource = ''): GoogleMerchantClient
    {
        return new GoogleMerchantClient(
            enabled: $enabled,
            merchantId: $merchantId,
            serviceAccountKeyPath: $keyPath,
            dataSource: $dataSource,
        );
    }

    #[Test]
    public function itReportsEnabledStateCorrectly(): void
    {
        $this->assertTrue($this->makeClient(enabled: true)->isEnabled());
        $this->assertFalse($this->makeClient(enabled: false)->isEnabled());
    }

    #[Test]
    public function itReturnsMerchantId(): void
    {
        $this->assertSame('12345', $this->makeClient(merchantId: '12345')->merchantId());
    }

    #[Test]
    public function itReturnsDataSource(): void
    {
        $this->assertSame('accounts/12345/dataSources/99', $this->makeClient(merchantId: '12345', dataSource: '99')->dataSource());
    }

    #[Test]
    public function itThrowsWhenKeyFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Google Merchant service account key not found at \[.+nonexistent.+\]/');

        $this->makeClient(keyPath: '/nonexistent/path.json')->client();
    }

    #[Test]
    public function itResolvesRelativePathsAgainstProjectRoot(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Google Merchant service account key not found at [' . base_path('google-merchant-key.json') . ']');

        $this->makeClient(keyPath: 'google-merchant-key.json')->client();
    }

    #[Test]
    public function itBuildsAnAuthenticatedClientFromKeyFile(): void
    {
        $keyPath = tempnam(sys_get_temp_dir(), 'gm_');
        file_put_contents($keyPath, json_encode([
            'type' => 'service_account',
            'project_id' => 'test-project',
            'private_key_id' => 'key-id',
            'private_key' => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA2a2rwplBQLzHPZe5TNJNV8E6YOoGMV4HHbJ9nMb/uPzqCzCt\ntest\n-----END RSA PRIVATE KEY-----\n",
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
            'client_id' => '123456789',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]));

        try {
            $client = $this->makeClient(keyPath: $keyPath)->client();

            $this->assertInstanceOf(ServiceAccountCredentials::class, $client);
        } finally {
            unlink($keyPath);
        }
    }

    #[Test]
    public function itIsResolvableFromTheContainer(): void
    {
        $client = app(GoogleMerchantClient::class);

        $this->assertInstanceOf(GoogleMerchantClient::class, $client);
    }

    #[Test]
    public function itIsAContainerSingleton(): void
    {
        $this->assertSame(app(GoogleMerchantClient::class), app(GoogleMerchantClient::class));
    }
}
