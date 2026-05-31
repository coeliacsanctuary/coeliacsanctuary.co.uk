<?php

declare(strict_types=1);

namespace Tests\Unit\Services\GoogleMerchant;

use App\Services\GoogleMerchant\GoogleMerchantClient;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use Google\Service\ShoppingContent;
use Google\Service\ShoppingContent\ShippingSettings;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleMerchantShippingManagerTest extends TestCase
{
    protected GoogleMerchantShippingManager $manager;

    /** @var ShoppingContent\Resource\Shippingsettings&Mockery\MockInterface */
    protected mixed $mockShippingsettings;

    protected GoogleMerchantClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->mock(GoogleMerchantClient::class);
        $this->client->shouldReceive('merchantId')->andReturn('12345');

        $this->mockShippingsettings = Mockery::mock(ShoppingContent\Resource\Shippingsettings::class);

        /** @var ShoppingContent $mockService */
        $mockService = Mockery::mock(ShoppingContent::class);
        $mockService->shippingsettings = $this->mockShippingsettings;

        $this->manager = new GoogleMerchantShippingManager($this->client);
        $this->manager->setShoppingContent($mockService);
    }

    #[Test]
    public function itDelegatesIsEnabledToTheClient(): void
    {
        $this->client->shouldReceive('isEnabled')->andReturn(true, false);

        $this->assertTrue($this->manager->isEnabled());
        $this->assertFalse($this->manager->isEnabled());
    }

    #[Test]
    public function itExposesTheMerchantId(): void
    {
        $this->assertSame('12345', $this->manager->merchantId());
    }

    #[Test]
    public function itUpdatesShippingSettings(): void
    {
        $settings = new ShippingSettings(['accountId' => '12345']);
        $returned = new ShippingSettings(['accountId' => '12345']);

        $this->mockShippingsettings
            ->shouldReceive('update')
            ->with('12345', '12345', $settings)
            ->once()
            ->andReturn($returned);

        $result = $this->manager->update($settings);

        $this->assertSame($returned, $result);
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
