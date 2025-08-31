<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AlterItemQuantityAction;
use App\Actions\Shop\CheckIfBasketHasDigitalProductsAction;
use App\Enums\Shop\ProductVariantType;
use App\Exceptions\QuantityException;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Database\Seeders\ShopScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class AlterItemQuantityActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create([
            'postage_country_id' => 1,
        ]);
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();
        $this->variant->update(['quantity' => 1]);

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);
    }

    #[Test]
    public function itThrowsAnExceptionIfTryingToIncreaseQuantityButStockIsntAvailable(): void
    {
        $this->variant->update(['quantity' => 0]);

        $this->expectException(QuantityException::class);

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');
    }

    #[Test]
    public function itTouchesTheOrder(): void
    {
        TestTime::freeze();
        TestTime::addMinutes(30);

        $this->assertFalse($this->order->refresh()->updated_at->isSameSecond(now()));

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');

        $this->assertTrue($this->order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itUpdatesTheItemQuantityWhenCallingToIncrease(): void
    {
        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');

        $this->assertEquals(2, $this->item->refresh()->quantity);
    }

    #[Test]
    public function itUpdatesTheRemainingVariantQuantityWhenCallingToIncrease(): void
    {
        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');

        $this->assertEquals(0, $this->variant->refresh()->quantity);
    }

    #[Test]
    public function itDoesntUpdateTheRemainingVariantQuantityWhenCallingToIncreaseWhenTheVariantIsDigitalOnly(): void
    {
        $this->variant->update(['variant_type' => ProductVariantType::DIGITAL]);

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');

        $this->assertEquals(1, $this->variant->refresh()->quantity);
    }

    #[Test]
    public function itCallsTheCheckIfBasketHasDigitalProductsActionWhenCallingToIncrease(): void
    {
        $this->mock(CheckIfBasketHasDigitalProductsAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($order) {
                $this->assertTrue($this->order->is($order));

                return true;
            })
            ->once();

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'increase');
    }

    #[Test]
    public function itUpdatesTheItemQuantityWhenCallingToDecrease(): void
    {
        $this->item->update(['quantity' => 2]);
        $this->callAction(AlterItemQuantityAction::class, $this->item, 'decrease');

        $this->assertEquals(1, $this->item->refresh()->quantity);
    }

    #[Test]
    public function itUpdatesTheRemainingVariantQuantityWhenCallingToDecrease(): void
    {
        $this->callAction(AlterItemQuantityAction::class, $this->item, 'decrease');

        $this->assertEquals(2, $this->variant->refresh()->quantity);
    }

    #[Test]
    public function itDoesntUpdateTheRemainingVariantQuantityWhenCallingToDecreaseWhenTheItemIsDigitalOnly(): void
    {
        $this->variant->update(['variant_type' => ProductVariantType::DIGITAL]);

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'decrease');

        $this->assertEquals(1, $this->variant->refresh()->quantity);
    }

    #[Test]
    public function itCallsTheCheckIfBasketHasDigitalProductsActionWhenCallingToDecrease(): void
    {
        $this->mock(CheckIfBasketHasDigitalProductsAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($order) {
                $this->assertTrue($this->order->is($order));

                return true;
            })
            ->once();

        $this->callAction(AlterItemQuantityAction::class, $this->item, 'decrease');
    }

    #[Test]
    public function itWillDeleteTheItemFromBasketIfTheRemainingQuantityIsZero(): void
    {
        $this->callAction(AlterItemQuantityAction::class, $this->item, 'decrease');

        $this->assertModelMissing($this->item);
    }
}
