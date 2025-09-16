<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\ReopenBasketAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReopenBasketActionTest extends TestCase
{
    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopOrder $basket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 2);

        $this->product = ShopProduct::query()->first();
        $this->variant = ShopProductVariant::query()->first();

        $this->product->update(['title' => 'My Product']);
        $this->variant->update(['title' => '']);

        $this->basket = $this->build(ShopOrder::class)
            ->forCustomer($this->create(ShopCustomer::class))
            ->asExpired()
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($this->variant, 2)
            ->toBasket($this->basket)
            ->create();
    }

    #[Test]
    public function itUpdatesTheBasketStateId(): void
    {
        $this->assertEquals(OrderState::EXPIRED, $this->basket->state_id);

        app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals(OrderState::BASKET, $this->basket->refresh()->state_id);
    }

    #[Test]
    public function itReturnsAMessageSayingAllItemsAreOutOfStockIfAllItemsAreOutOfStock(): void
    {
        $this->variant->update(['quantity' => 0]);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('All of the items in your basket have gone out of stock', $warnings->first());
    }

    #[Test]
    public function itReturnsAMessageSayingAllItemsAreOutOfStockIfAllItemsAreOutOfStockIfItIsABundleVariant(): void
    {
        $this->variant->update(['quantity' => 0]);

        $bundleVariant = $this->build(ShopProductVariant::class)
            ->has($this->build(ShopPrice::class), 'prices')
            ->belongsToProduct($this->product)
            ->isBundle()
            ->create([
                'quantity' => 100,
            ]);

        $this->basket->items->first()->update(['product_variant_id' => $bundleVariant->id]);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('All of the items in your basket have gone out of stock', $warnings->first());
    }

    #[Test]
    public function itReturnsAMessageIfTheProductDoesntHaveEnoughQuantity(): void
    {
        $this->variant->update(['quantity' => 1]);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('My Product only has 1 item left', $warnings->first());
    }

    #[Test]
    public function itReturnsAMessageWithTheVariantDetailsIfTheProductDoesntHaveEnoughQuantityAndHasUniqueVariants(): void
    {
        $this->variant->update([
            'quantity' => 1,
            'title' => 'blue',
        ]);

        $this->product->update(['variant_title' => 'colour']);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('My Product only has 1 item left in the blue colour', $warnings->first());
    }

    #[Test]
    public function itDoesntReturnAnyWarningsIfThereIsEnoughStockAvailable(): void
    {
        $this->variant->update(['quantity' => 5]);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEmpty($warnings);
    }

    #[Test]
    public function itDoesntCallTheAddToBasketActionIfThereIsNoStockAvailable(): void
    {
        $this->dontExpectAction(AddProductToBasketAction::class);

        $this->variant->update(['quantity' => 0]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itCallsTheAddToBasketActionWithTheCorrectRemainingQuantityWhenNotAllQuantityIsAvailable(): void
    {
        $this->variant->update(['quantity' => 1]);

        $args = [
            'basket' => $this->basket,
            'product' => $this->product,
            'variant' => $this->variant,
            'quantity' => 1,
        ];

        $this->expectAction(AddProductToBasketAction::class, [function (...$params) use ($args) {
            foreach (array_values($args) as $index => $value) {
                if ($value instanceof Model) {
                    $this->assertTrue($value->is($params[$index]));

                    continue;
                }

                $this->assertEquals($value, $params[$index]);
            }

            return true;
        }]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itCallsTheAddToBasketActionWithTheCorrectValuesWhenEverythingIsAvailable(): void
    {
        $args = [
            'basket' => $this->basket,
            'product' => $this->product,
            'variant' => $this->variant,
            'quantity' => 2,
        ];

        $this->expectAction(AddProductToBasketAction::class, [function (...$params) use ($args) {
            foreach (array_values($args) as $index => $value) {
                if ($value instanceof Model) {
                    $this->assertTrue($value->is($params[$index]));

                    continue;
                }

                $this->assertEquals($value, $params[$index]);
            }

            return true;
        }]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itDisplaysAWarningWhenOneItemOutOfManyHasOneOutOfStock(): void
    {
        $otherVariant = ShopProductVariant::query()->latest()->where('id', '!=', $this->variant->id)->first();

        $this->build(ShopOrderItem::class)
            ->add($otherVariant)
            ->toBasket($this->basket)
            ->create();

        $this->assertCount(2, $this->basket->refresh()->items);

        $otherVariant->update(['quantity' => 0, 'title' => '']);
        $otherVariant->product->update(['title' => 'Other Product']);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('Other Product has gone out of stock', $warnings->first());
    }

    #[Test]
    public function itDisplaysAWarningWhenOneItemOutOfManyHasOneOutOfStockWithACustomVariant(): void
    {
        $otherVariant = ShopProductVariant::query()->latest()->where('id', '!=', $this->variant->id)->first();

        $this->build(ShopOrderItem::class)
            ->add($otherVariant)
            ->toBasket($this->basket)
            ->create();

        $this->assertCount(2, $this->basket->refresh()->items);

        $otherVariant->update(['quantity' => 0, 'title' => 'Red']);
        $otherVariant->product->update(['title' => 'Other Product']);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('Other Product in Red has gone out of stock', $warnings->first());
    }

    #[Test]
    public function itOnlyCallsTheAddToBasketActionForTheInStockItems(): void
    {
        $otherVariant = ShopProductVariant::query()->latest()->where('id', '!=', $this->variant->id)->first();

        $this->build(ShopOrderItem::class)
            ->add($otherVariant)
            ->toBasket($this->basket)
            ->create();

        $this->assertCount(2, $this->basket->refresh()->items);

        $otherVariant->update(['quantity' => 0, 'title' => 'Red']);
        $otherVariant->product->update(['title' => 'Other Product']);

        $this->expectAction(AddProductToBasketAction::class, once: true);

        app(ReopenBasketAction::class)->handle($this->basket);
    }
}
