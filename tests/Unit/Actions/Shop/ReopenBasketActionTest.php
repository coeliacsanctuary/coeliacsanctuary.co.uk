<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddDigitalCardToBasketAction;
use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\ReopenBasketAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
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

    protected ShopOrderItem $item;

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

        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)
            ->add($this->variant, 2)
            ->toBasket($this->basket)
            ->create();

        $this->item = $item;
    }

    protected function digitalItem(?ShopOrderItem $orderItem = null): ShopOrderDigitalItem
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create();

        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->basket)
            ->forCard($card)
            ->createQuietly(['physical_order_item_id' => $orderItem?->id]);

        return $item;
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

    #[Test]
    public function itCallsTheAddDigitalCardToBasketActionForEachDigitalCardInTheBasket(): void
    {
        $this->digitalItem();
        $this->digitalItem();

        $this->expectAction(AddDigitalCardToBasketAction::class, once: false, then: fn ($mock) => $mock->twice());

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itDoesntCallTheAddDigitalCardToBasketActionWhenTheBasketHasNoDigitalCards(): void
    {
        $this->dontExpectAction(AddDigitalCardToBasketAction::class);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itCallsTheAddDigitalCardToBasketActionWithTheCardAndNoOrderItem(): void
    {
        $digitalItem = $this->digitalItem();

        $this->expectAction(AddDigitalCardToBasketAction::class, [function (ShopOrder $basket, ShopDigitalCard $card, ?ShopOrderItem $orderItem) use ($digitalItem) {
            $this->assertTrue($basket->is($this->basket));
            $this->assertTrue($card->is($digitalItem->card));
            $this->assertNull($orderItem);

            return true;
        }]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itCallsTheAddDigitalCardToBasketActionWithTheLinkedPhysicalOrderItem(): void
    {
        $this->digitalItem($this->item);

        $this->expectAction(AddDigitalCardToBasketAction::class, [function (ShopOrder $basket, ShopDigitalCard $card, ?ShopOrderItem $orderItem) {
            $this->assertNotNull($orderItem);
            $this->assertTrue($orderItem->is($this->item));

            return true;
        }]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itDoesntSayEverythingIsOutOfStockWhenTheBasketStillHasADigitalCard(): void
    {
        $this->variant->update(['quantity' => 0]);

        $this->digitalItem();

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('My Product has gone out of stock', $warnings->first());
    }

    #[Test]
    public function itStillAddsTheDigitalCardsWhenAllOfThePhysicalItemsAreOutOfStock(): void
    {
        $this->variant->update(['quantity' => 0]);

        $this->digitalItem();

        $this->dontExpectAction(AddProductToBasketAction::class);
        $this->expectAction(AddDigitalCardToBasketAction::class);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itReopensABasketThatOnlyContainsDigitalCards(): void
    {
        $this->item->delete();

        $this->digitalItem();

        $this->expectAction(AddDigitalCardToBasketAction::class);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEmpty($warnings);
        $this->assertEquals(OrderState::BASKET, $this->basket->refresh()->state_id);
    }

    #[Test]
    public function itSaysEverythingIsOutOfStockWhenTheBasketHasNoItemsAtAll(): void
    {
        $this->item->delete();

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals('All of the items in your basket have gone out of stock', $warnings->first());
    }

    #[Test]
    public function itWarnsThatACardIsNoLongerAvailableWhenItIsntLive(): void
    {
        $this->variant->update(['quantity' => 5]);

        $digitalItem = $this->digitalItem();

        $digitalItem->card->update(['live' => false]);

        $warnings = app(ReopenBasketAction::class)->handle($this->basket);

        $this->assertEquals("Sorry, {$digitalItem->digital_card_title} is no longer available.", $warnings->first());
    }

    #[Test]
    public function itDoesntAddACardToTheBasketWhenItIsntLive(): void
    {
        $this->digitalItem()->card->update(['live' => false]);

        $this->dontExpectAction(AddDigitalCardToBasketAction::class);

        app(ReopenBasketAction::class)->handle($this->basket);
    }

    #[Test]
    public function itStillAddsTheLiveCardsWhenAnotherCardIsntLive(): void
    {
        $this->digitalItem()->card->update(['live' => false]);

        $liveItem = $this->digitalItem();

        $this->expectAction(AddDigitalCardToBasketAction::class, [function (ShopOrder $basket, ShopDigitalCard $card, ?ShopOrderItem $orderItem) use ($liveItem) {
            $this->assertTrue($card->is($liveItem->card));

            return true;
        }]);

        app(ReopenBasketAction::class)->handle($this->basket);
    }
}
