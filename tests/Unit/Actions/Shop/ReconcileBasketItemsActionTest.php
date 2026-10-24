<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\ReconcileBasketItemsAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReconcileBasketItemsActionTest extends TestCase
{
    protected ShopOrder $basket;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopOrder $basket */
        $basket = $this->create(ShopOrder::class);

        $this->basket = $basket;
    }

    protected function product(): ShopProduct
    {
        /** @var ShopProduct $product */
        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)->belongsToProduct($product)->create();

        return $product;
    }

    protected function card(?ShopProduct $product = null, ?int $price = 500, int $priceWithPhysicalProduct = 50): ShopDigitalCard
    {
        $factory = $this->build(ShopDigitalCard::class);

        if ($product) {
            $factory = $factory->forProduct($product);
        }

        /** @var ShopDigitalCard $card */
        $card = $factory->create();

        if ($price !== null) {
            $this->build(ShopDigitalCardPrice::class)->forCard($card)->create([
                'price' => $price,
                'price_with_physical_product' => $priceWithPhysicalProduct,
            ]);
        }

        return $card;
    }

    protected function digitalItem(ShopDigitalCard $card, array $attributes = []): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->basket)
            ->forCard($card)
            ->create($attributes);

        return $item;
    }

    protected function basketItem(ShopProduct $product): ShopOrderItem
    {
        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)->inOrder($this->basket)->inProduct($product)->create();

        return $item;
    }

    #[Test]
    public function itDoesntChangeAnItemWhoseCardIsntLinkedToAProduct(): void
    {
        $item = $this->digitalItem($this->card(), ['digital_card_price' => 50]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(50, $item->refresh()->digital_card_price);
        $this->assertNull($item->physical_order_item_id);
    }

    #[Test]
    public function itResetsThePriceToTheStandardPriceWhenTheLinkedProductIsntInTheBasket(): void
    {
        $item = $this->digitalItem($this->card($this->product()), ['digital_card_price' => 50]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(500, $item->refresh()->digital_card_price);
    }

    #[Test]
    public function itClearsTheLinkToThePhysicalItemWhenTheLinkedProductIsntInTheBasket(): void
    {
        $product = $this->product();
        $card = $this->card($product);

        $physicalItem = $this->basketItem($product);

        $item = $this->digitalItem($card, [
            'digital_card_price' => 50,
            'physical_order_item_id' => $physicalItem->id,
        ]);

        $physicalItem->delete();

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertNull($item->refresh()->physical_order_item_id);
        $this->assertEquals(500, $item->digital_card_price);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenTheCardHasNoPriceAndTheProductIsntInTheBasket(): void
    {
        $item = $this->digitalItem($this->card($this->product(), price: null), ['digital_card_price' => 50]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(ShopDigitalCard::defaultPrice()->price, $item->refresh()->digital_card_price);
    }

    #[Test]
    public function itLeavesACorrectlyPricedAndUnlinkedItemAlone(): void
    {
        $item = $this->digitalItem($this->card($this->product()), ['digital_card_price' => 500]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(500, $item->refresh()->digital_card_price);
        $this->assertNull($item->physical_order_item_id);
    }

    #[Test]
    public function itSetsTheBundlePriceWhenTheLinkedProductIsInTheBasket(): void
    {
        $product = $this->product();

        $item = $this->digitalItem($this->card($product), ['digital_card_price' => 500]);

        $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(50, $item->refresh()->digital_card_price);
    }

    #[Test]
    public function itLinksTheItemToThePhysicalItemWhenTheLinkedProductIsInTheBasket(): void
    {
        $product = $this->product();

        $item = $this->digitalItem($this->card($product), ['digital_card_price' => 500]);

        $physicalItem = $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals($physicalItem->id, $item->refresh()->physical_order_item_id);
    }

    #[Test]
    public function itRelinksTheItemWhenItPointsAtADifferentPhysicalItem(): void
    {
        $product = $this->product();
        $card = $this->card($product);

        $oldPhysicalItem = $this->basketItem($this->product());

        $item = $this->digitalItem($card, [
            'digital_card_price' => 50,
            'physical_order_item_id' => $oldPhysicalItem->id,
        ]);

        $physicalItem = $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals($physicalItem->id, $item->refresh()->physical_order_item_id);
    }

    #[Test]
    public function itFallsBackToTheDefaultBundlePriceWhenTheCardHasNoPrice(): void
    {
        $product = $this->product();

        $item = $this->digitalItem($this->card($product, price: null), ['digital_card_price' => 500]);

        $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(
            ShopDigitalCard::defaultPrice()->price_with_physical_product,
            $item->refresh()->digital_card_price,
        );
    }

    #[Test]
    public function itLeavesAnAlreadyBundledItemAlone(): void
    {
        $product = $this->product();

        $physicalItem = $this->basketItem($product);

        $item = $this->digitalItem($this->card($product), [
            'digital_card_price' => 50,
            'physical_order_item_id' => $physicalItem->id,
        ]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(50, $item->refresh()->digital_card_price);
        $this->assertEquals($physicalItem->id, $item->physical_order_item_id);
    }

    #[Test]
    public function itReconcilesEveryDigitalItemInTheBasket(): void
    {
        $product = $this->product();

        $bundled = $this->digitalItem($this->card($product), ['digital_card_price' => 500]);
        $standalone = $this->digitalItem($this->card($this->product()), ['digital_card_price' => 50]);

        $physicalItem = $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(50, $bundled->refresh()->digital_card_price);
        $this->assertEquals($physicalItem->id, $bundled->physical_order_item_id);

        $this->assertEquals(500, $standalone->refresh()->digital_card_price);
        $this->assertNull($standalone->physical_order_item_id);
    }

    #[Test]
    public function itOnlyReconcilesTheGivenBasket(): void
    {
        $product = $this->product();
        $card = $this->card($product);

        /** @var ShopOrder $otherBasket */
        $otherBasket = $this->create(ShopOrder::class);

        /** @var ShopOrderDigitalItem $otherItem */
        $otherItem = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($otherBasket)
            ->forCard($card)
            ->createQuietly(['digital_card_price' => 50]);

        $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertEquals(50, $otherItem->refresh()->digital_card_price);
    }

    #[Test]
    public function itFlagsTheBasketAsHavingDigitalItems(): void
    {
        $this->digitalItem($this->card(), ['digital_card_price' => 50]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertTrue($this->basket->refresh()->has_digital_items);
    }

    #[Test]
    public function itFlagsTheBasketAsDigitalOnlyWhenItHasNoPhysicalItems(): void
    {
        $this->digitalItem($this->card(), ['digital_card_price' => 50]);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->assertTrue($this->basket->refresh()->is_digital_only);
    }

    #[Test]
    public function itDoesntFlagTheBasketAsDigitalOnlyWhenItHasPhysicalItems(): void
    {
        $product = $this->product();

        $this->digitalItem($this->card($product), ['digital_card_price' => 500]);
        $this->basketItem($product);

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->basket->refresh();

        $this->assertTrue($this->basket->has_digital_items);
        $this->assertFalse($this->basket->is_digital_only);
    }

    #[Test]
    public function itClearsTheDigitalFlagsWhenTheBasketHasNoDigitalItems(): void
    {
        $this->basket->updateQuietly(['has_digital_items' => true, 'is_digital_only' => true]);

        $this->basketItem($this->product());

        $this->callAction(ReconcileBasketItemsAction::class, $this->basket);

        $this->basket->refresh();

        $this->assertFalse($this->basket->has_digital_items);
        $this->assertFalse($this->basket->is_digital_only);
    }
}
