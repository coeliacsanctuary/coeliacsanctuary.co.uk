<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\PrepareOrderForEmailAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrepareOrderForEmailActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $this->order = $order;
        $this->product = $product;
    }

    protected function card(array $attributes = []): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)
            ->forProduct($this->product)
            ->state($attributes)
            ->create();

        return $card;
    }

    protected function orderItem(?ShopProduct $product = null): ShopOrderItem
    {
        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->inProduct($product ?? $this->product)
            ->create();

        return $item;
    }

    protected function digitalItem(?ShopDigitalCard $card = null, ?ShopOrderItem $bundledTo = null): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->forCard($card ?? $this->card())
            ->create(['physical_order_item_id' => $bundledTo?->id]);

        return $item;
    }

    protected function attachTerm(string $term, string $type = 'country', string $language = 'french', int $score = 10, bool $showOnProductPage = true): void
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => $type]);

        $this->product->travelCardSearchTerms()->attach($searchTerm, [
            'card_language' => $language,
            'card_score' => $score,
            'card_show_on_product_page' => $showOnProductPage,
        ]);
    }

    protected function prepare(): ShopOrder
    {
        return $this->callAction(PrepareOrderForEmailAction::class, $this->order);
    }

    #[Test]
    public function itReturnsTheOrderItWasGiven(): void
    {
        $this->assertSame($this->order, $this->prepare());
    }

    #[Test]
    public function itLoadsTheOrderRelationsNeededByTheEmail(): void
    {
        $order = $this->prepare();

        foreach (['address', 'customer', 'items', 'payment', 'refunds', 'digitalItems'] as $relation) {
            $this->assertTrue($order->relationLoaded($relation));
        }
    }

    #[Test]
    public function itLoadsTheProductAndVariantOnEachItem(): void
    {
        $this->orderItem();

        $item = $this->prepare()->items->first();

        $this->assertTrue($item->relationLoaded('product'));
        $this->assertTrue($item->relationLoaded('variant'));
        $this->assertTrue($item->relationLoaded('digitalBundles'));
    }

    #[Test]
    public function itLoadsTheCardAndItsRelationsOnEachDigitalItem(): void
    {
        $this->digitalItem();

        $item = $this->prepare()->digitalItems->first();

        $this->assertTrue($item->relationLoaded('card'));
        $this->assertTrue($item->card->relationLoaded('price'));
        $this->assertTrue($item->card->relationLoaded('product'));
        $this->assertTrue($item->card->product->relationLoaded('travelCardSearchTerms'));
    }

    #[Test]
    public function itLoadsTheCardAndItsRelationsOnEachBundledDigitalItem(): void
    {
        $this->digitalItem(bundledTo: $this->orderItem());

        $bundle = $this->prepare()->items->first()->digitalBundles->first();

        $this->assertTrue($bundle->relationLoaded('card'));
        $this->assertTrue($bundle->card->relationLoaded('price'));
        $this->assertTrue($bundle->card->relationLoaded('product'));
        $this->assertTrue($bundle->card->product->relationLoaded('travelCardSearchTerms'));
    }

    #[Test]
    public function itKeepsThePriceAssignedToADigitalItemsCard(): void
    {
        $card = $this->card();

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->digitalItem($card);

        $this->assertSame(500, $this->prepare()->digitalItems->first()->card->price->price);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenADigitalItemsCardDoesntHaveOne(): void
    {
        $this->digitalItem();

        $this->assertTrue($this->prepare()->digitalItems->first()->card->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenABundledDigitalItemsCardDoesntHaveOne(): void
    {
        $this->digitalItem(bundledTo: $this->orderItem());

        $card = $this->prepare()->items->first()->digitalBundles->first()->card;

        $this->assertTrue($card->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itAssignsTheProductsCountriesToADigitalItemsCard(): void
    {
        $this->digitalItem($this->card(['language' => 'french']));

        $this->attachTerm('france');

        $this->assertSame(['france'], $this->prepare()->digitalItems->first()->card->countries->pluck('term')->all());
    }

    #[Test]
    public function itAssignsTheProductsCountriesToABundledDigitalItemsCard(): void
    {
        $this->digitalItem($this->card(['language' => 'french']), $this->orderItem());

        $this->attachTerm('france');

        $card = $this->prepare()->items->first()->digitalBundles->first()->card;

        $this->assertSame(['france'], $card->countries->pluck('term')->all());
    }

    #[Test]
    public function itFlagsThePhysicalProductAsInTheOrderWhenTheOrderContainsTheCardsProduct(): void
    {
        $this->digitalItem();

        $this->orderItem();

        $this->assertTrue($this->prepare()->digitalItems->first()->card->getAttribute('physicalProductInBasket'));
    }

    #[Test]
    public function itFlagsThePhysicalProductAsNotInTheOrderWhenTheOrderOnlyContainsOtherProducts(): void
    {
        $this->digitalItem();

        /** @var ShopProduct $otherProduct */
        $otherProduct = $this->create(ShopProduct::class);

        $this->orderItem($otherProduct);

        $this->assertFalse($this->prepare()->digitalItems->first()->card->getAttribute('physicalProductInBasket'));
    }

    #[Test]
    public function itHandlesAnOrderWithNoItems(): void
    {
        $order = $this->prepare();

        $this->assertCount(0, $order->items);
        $this->assertCount(0, $order->digitalItems);
    }
}
