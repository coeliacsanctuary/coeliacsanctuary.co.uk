<?php

declare(strict_types=1);

namespace Tests\Unit\Services\GoogleMerchant;

use App\Services\GoogleMerchant\GoogleMerchantClient;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleMerchantProductManagerTest extends TestCase
{
    #[Test]
    public function itDelegatesIsEnabledToTheClient(): void
    {
        $client = $this->mock(GoogleMerchantClient::class);
        $client->shouldReceive('isEnabled')->andReturn(true, false);

        $manager = new GoogleMerchantProductManager($client);

        $this->assertTrue($manager->isEnabled());
        $this->assertFalse($manager->isEnabled());
    }

    #[Test]
    public function itIsResolvableFromTheContainer(): void
    {
        $this->assertInstanceOf(GoogleMerchantProductManager::class, app(GoogleMerchantProductManager::class));
    }

    #[Test]
    public function itIsAContainerSingleton(): void
    {
        $this->assertSame(app(GoogleMerchantProductManager::class), app(GoogleMerchantProductManager::class));
    }
}
