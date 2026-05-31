<?php

declare(strict_types=1);

namespace Tests\Unit\Services\GoogleMerchant;

use App\Services\GoogleMerchant\GoogleMerchantClient;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Google\Service\ShoppingContent;
use Google\Service\ShoppingContent\Product;
use Google\Service\ShoppingContent\Resource\Products;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleMerchantProductManagerTest extends TestCase
{
    protected GoogleMerchantProductManager $manager;

    protected Products $mockProducts;

    protected GoogleMerchantClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->mock(GoogleMerchantClient::class);
        $this->client->shouldReceive('merchantId')->andReturn('12345');

        /** @var Products $mockProducts */
        $mockProducts = Mockery::mock(Products::class);
        $this->mockProducts = $mockProducts;

        /** @var ShoppingContent $mockService */
        $mockService = Mockery::mock(ShoppingContent::class);
        $mockService->products = $this->mockProducts;

        $this->manager = new GoogleMerchantProductManager($this->client);
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
    public function itInsertsAProduct(): void
    {
        $product = new Product(['offerId' => '1']);
        $returned = new Product(['id' => 'online:en:GB:1']);

        $this->mockProducts
            ->shouldReceive('insert')
            ->with('12345', $product)
            ->once()
            ->andReturn($returned);

        $result = $this->manager->insert($product);

        $this->assertSame($returned, $result);
    }

    #[Test]
    public function itUpdatesAProduct(): void
    {
        $product = new Product(['offerId' => '1']);
        $returned = new Product(['id' => 'online:en:GB:1']);

        $this->mockProducts
            ->shouldReceive('update')
            ->with('12345', 'online:en:GB:1', $product)
            ->once()
            ->andReturn($returned);

        $result = $this->manager->update('online:en:GB:1', $product);

        $this->assertSame($returned, $result);
    }

    #[Test]
    public function itDeletesAProduct(): void
    {
        $this->mockProducts
            ->shouldReceive('delete')
            ->with('12345', 'online:en:GB:1')
            ->once();

        $this->manager->delete('online:en:GB:1');
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
