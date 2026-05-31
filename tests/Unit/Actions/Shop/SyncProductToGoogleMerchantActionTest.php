<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Google\Service\ShoppingContent\Product;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncProductToGoogleMerchantActionTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(categories: 1, products: 1, variants: 2);

        $this->product = ShopProduct::query()->first();
    }

    #[Test]
    public function itDoesNothingWhenGlobalMerchantIsDisabled(): void
    {
        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')
            ->andReturn(false)
            ->shouldNotReceive('insert')
            ->shouldNotReceive('update')
            ->shouldNotReceive('delete');

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itDeletesAndClearsIdWhenProductMerchantIsDisabledAndHasAnExistingId(): void
    {
        $this->product->update([
            'google_merchant_enabled' => false,
            'google_merchant_product_id' => 'online:en:GB:1',
        ]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('delete')->with('online:en:GB:1')->once();

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertNull($this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itJustReturnsWhenProductMerchantIsDisabledAndHasNoExistingId(): void
    {
        $this->product->update(['google_merchant_enabled' => false]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldNotReceive('delete')
            ->shouldNotReceive('insert')
            ->shouldNotReceive('update');

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itDeletesAndClearsIdWhenProductHasNoLiveStockAndHasAnExistingId(): void
    {
        $this->product->update(['google_merchant_product_id' => 'online:en:GB:1']);
        ShopProductVariant::query()->update(['quantity' => 0]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('delete')->with('online:en:GB:1')->once();

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertNull($this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itJustReturnsWhenProductHasNoLiveStockAndNoExistingId(): void
    {
        ShopProductVariant::query()->update(['quantity' => 0]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldNotReceive('delete')
            ->shouldNotReceive('insert')
            ->shouldNotReceive('update');

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itInsertsWhenProductIsInStockAndHasNoExistingId(): void
    {
        $returned = new Product(['id' => 'online:en:GB:' . $this->product->id]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')->once()->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertSame('online:en:GB:' . $this->product->id, $this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itUpdatesWhenProductIsInStockAndHasAnExistingId(): void
    {
        $existingId = 'online:en:GB:' . $this->product->id;
        $this->product->update(['google_merchant_product_id' => $existingId]);

        $returned = new Product(['id' => $existingId]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('update')->with($existingId, Mockery::type(Product::class))->once()->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertSame($existingId, $this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itUsesTheProductTitleAsTheMerchantTitle(): void
    {
        $returned = new Product(['id' => 'online:en:GB:' . $this->product->id]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (Product $product) => $product->getTitle() === $this->product->title)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }
}
