<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Google\Shopping\Merchant\Products\V1\Condition;
use Google\Shopping\Merchant\Products\V1\ProductInput;
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
            'google_merchant_product_id' => 'accounts/12345/productInputs/ZW4~GB~1',
        ]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('delete')->with('accounts/12345/productInputs/ZW4~GB~1')->once();

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
        $this->product->update(['google_merchant_product_id' => 'accounts/12345/productInputs/ZW4~GB~1']);
        ShopProductVariant::query()->update(['quantity' => 0]);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('delete')->with('accounts/12345/productInputs/ZW4~GB~1')->once();

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
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')->once()->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertSame('accounts/12345/productInputs/ZW4~GB~' . $this->product->id, $this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itUpdatesWhenProductIsInStockAndHasAnExistingId(): void
    {
        $existingId = 'accounts/12345/productInputs/ZW4~GB~' . $this->product->id;
        $this->product->update(['google_merchant_product_id' => $existingId]);

        $returned = (new ProductInput())->setName($existingId);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('update')->with($existingId, Mockery::type(ProductInput::class))->once()->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);

        $this->assertSame($existingId, $this->product->fresh()->google_merchant_product_id);
    }

    #[Test]
    public function itUsesTheProductTitleAsTheMerchantTitle(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getTitle() === $this->product->title)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsTheShippingWeightFromTheFirstVariantInKg(): void
    {
        $variant = ShopProductVariant::query()->first();
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getShippingWeight()->getValue() === $variant->weight / 1000
                    && $input->getProductAttributes()->getShippingWeight()->getUnit() === 'kg')
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsTheDescriptionFromTheProductDescription(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getDescription() === $this->product->description)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsTheBrandToCoeliacSanctuary(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getBrand() === 'Coeliac Sanctuary')
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsTheMpnFromTheProductSlug(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getMpn() === $this->product->slug)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsTheConditionToNew(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getCondition() === Condition::PBNEW)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsThePriceWhenNotOnSale(): void
    {
        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getPrice()->getAmountMicros() === $this->product->currentPrice * 10000
                    && $input->getProductAttributes()->getSalePrice() === null)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }

    #[Test]
    public function itSetsOriginalPriceAndSalePriceWhenProductIsOnSale(): void
    {
        $originalPrice = $this->product->currentPrice;

        $this->build(ShopPrice::class)
            ->forProduct($this->product)
            ->onSale()
            ->create(['start_at' => now()]);

        $this->product->unsetRelation('prices');

        $returned = (new ProductInput())->setName('accounts/12345/productInputs/ZW4~GB~' . $this->product->id);

        $this->mock(GoogleMerchantProductManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('insert')
            ->withArgs(fn (ProductInput $input) => $input->getProductAttributes()->getPrice()->getAmountMicros() === $originalPrice * 10000
                    && $input->getProductAttributes()->getSalePrice()->getAmountMicros() === $this->product->currentPrice * 10000)
            ->once()
            ->andReturn($returned);

        $this->callAction(SyncProductToGoogleMerchantAction::class, $this->product);
    }
}
