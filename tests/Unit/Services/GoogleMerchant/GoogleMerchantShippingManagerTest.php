<?php

declare(strict_types=1);

namespace Tests\Unit\Services\GoogleMerchant;

use App\Services\GoogleMerchant\GoogleMerchantClient;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleMerchantShippingManagerTest extends TestCase
{
    #[Test]
    public function itDelegatesIsEnabledToTheClient(): void
    {
        $client = $this->mock(GoogleMerchantClient::class);
        $client->shouldReceive('isEnabled')->andReturn(true, false);

        $manager = new GoogleMerchantShippingManager($client);

        $this->assertTrue($manager->isEnabled());
        $this->assertFalse($manager->isEnabled());
    }

    #[Test]
    public function itExposesTheMerchantId(): void
    {
        $client = $this->mock(GoogleMerchantClient::class);
        $client->shouldReceive('merchantId')->andReturn('12345');

        $manager = new GoogleMerchantShippingManager($client);

        $this->assertSame('12345', $manager->merchantId());
    }

    #[Test]
    public function itIsResolvableFromTheContainer(): void
    {
        $this->assertInstanceOf(GoogleMerchantShippingManager::class, app(GoogleMerchantShippingManager::class));
    }

    #[Test]
    public function itIsAContainerSingleton(): void
    {
        $this->assertSame(app(GoogleMerchantShippingManager::class), app(GoogleMerchantShippingManager::class));
    }
}
