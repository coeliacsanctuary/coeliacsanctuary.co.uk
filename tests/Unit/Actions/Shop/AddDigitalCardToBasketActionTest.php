<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddDigitalCardToBasketAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class AddDigitalCardToBasketActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        $this->order = $order;
        $this->card = $this->card('French Travel Card', 500);
    }

    protected function card(string $title, int $price, int $priceWithPhysicalProduct = 50): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create(['title' => $title]);

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create([
            'price' => $price,
            'price_with_physical_product' => $priceWithPhysicalProduct,
        ]);

        return $card;
    }

    protected function physicalItem(): ShopOrderItem
    {
        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)->inOrder($this->order)->create();

        return $item;
    }

    #[Test]
    public function itCreatesADigitalItemAgainstTheBasketRecord(): void
    {
        $this->assertDatabaseEmpty(ShopOrderDigitalItem::class);

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        $this->assertDatabaseCount(ShopOrderDigitalItem::class, 1);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertTrue($item->order->is($this->order));
        $this->assertTrue($item->card->is($this->card));
    }

    #[Test]
    public function itStoresTheTitleAndPriceOfTheCardAgainstTheItem(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals('French Travel Card', $item->digital_card_title);
        $this->assertEquals(500, $item->digital_card_price);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenTheCardDoesntHaveOne(): void
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create();

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $card, null);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals(ShopDigitalCard::defaultPrice()->price, $item->digital_card_price);
    }

    #[Test]
    public function itDoesntAddTheSameCardToTheBasketTwice(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        $this->assertDatabaseCount(ShopOrderDigitalItem::class, 1);
    }

    #[Test]
    public function itDoesntUpdateTheStoredTitleAndPriceOfACardAlreadyInTheBasket(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        $this->card->update(['title' => 'German Travel Card']);
        $this->card->price->update(['price' => 900]);

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card->refresh(), null);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals('French Travel Card', $item->digital_card_title);
        $this->assertEquals(500, $item->digital_card_price);
    }

    #[Test]
    public function itCreatesAnItemForEachDifferentCardAddedToTheBasket(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card('German Travel Card', 700), null);

        $this->assertDatabaseCount(ShopOrderDigitalItem::class, 2);

        $this->assertEquals(
            ['French Travel Card', 'German Travel Card'],
            $this->order->digitalItems()->pluck('digital_card_title')->all(),
        );
    }

    #[Test]
    public function itAddsTheCardToTheGivenBasketOnly(): void
    {
        /** @var ShopOrder $otherOrder */
        $otherOrder = $this->create(ShopOrder::class);

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        $this->assertCount(1, $this->order->digitalItems()->get());
        $this->assertCount(0, $otherOrder->digitalItems()->get());
    }

    #[Test]
    public function itDoesntLinkTheItemToAPhysicalOrderItemByDefault(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertNull($item->physical_order_item_id);
    }

    #[Test]
    public function itLinksTheItemToTheGivenPhysicalOrderItem(): void
    {
        $orderItem = $this->physicalItem();

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, $orderItem);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals($orderItem->id, $item->physical_order_item_id);
    }

    #[Test]
    public function itStoresTheWithPhysicalProductPriceWhenLinkedToAPhysicalOrderItem(): void
    {
        $card = $this->card('German Travel Card', 700, 75);

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $card, $this->physicalItem());

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals(75, $item->digital_card_price);
    }

    #[Test]
    public function itFallsBackToTheDefaultWithPhysicalProductPriceWhenTheCardDoesntHaveAPrice(): void
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create();

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $card, $this->physicalItem());

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals(ShopDigitalCard::defaultPrice()->price_with_physical_product, $item->digital_card_price);
    }

    #[Test]
    public function itLinksACardThatIsAlreadyInTheBasketToAPhysicalOrderItem(): void
    {
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        $orderItem = $this->physicalItem();

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, $orderItem);

        $this->assertDatabaseCount(ShopOrderDigitalItem::class, 1);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals($orderItem->id, $item->physical_order_item_id);
        $this->assertEquals(50, $item->digital_card_price);
    }

    #[Test]
    public function itKeepsTheExistingLinkWhenTheCardIsAddedAgainWithoutAPhysicalOrderItem(): void
    {
        $orderItem = $this->physicalItem();

        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, $orderItem);
        $this->callAction(AddDigitalCardToBasketAction::class, $this->order, $this->card, null);

        /** @var ShopOrderDigitalItem $item */
        $item = ShopOrderDigitalItem::query()->first();

        $this->assertEquals($orderItem->id, $item->physical_order_item_id);
        $this->assertEquals(50, $item->digital_card_price);
    }

    #[Test]
    public function itTouchesTheOrder(): void
    {
        TestTime::freeze()->subDay();

        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        $updatedAt = $order->updated_at;

        TestTime::addDay();

        $this->callAction(AddDigitalCardToBasketAction::class, $order, $this->card, null);

        $this->assertTrue($order->refresh()->updated_at->isAfter($updatedAt));
        $this->assertTrue(now()->isSameSecond($order->updated_at));
    }
}
