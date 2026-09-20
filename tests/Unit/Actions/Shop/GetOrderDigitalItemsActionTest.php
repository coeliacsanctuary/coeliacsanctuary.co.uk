<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetOrderDigitalItemsAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetOrderDigitalItemsActionTest extends TestCase
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

    protected function item(?ShopDigitalCard $card = null, ?ShopOrder $order = null): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order ?? $this->order)
            ->forCard($card ?? $this->card())
            ->create();

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

    protected function items(): AnonymousResourceCollection
    {
        return $this->callAction(GetOrderDigitalItemsAction::class, $this->order);
    }

    #[Test]
    public function itReturnsACollectionOfDigitalItemResources(): void
    {
        $this->item();

        $items = $this->items();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $items);

        $items->each(function ($item): void {
            $this->assertInstanceOf(ShopOrderDigitalItemResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsNothingWhenTheOrderHasNoDigitalItems(): void
    {
        $this->assertCount(0, $this->items());
    }

    #[Test]
    public function itOnlyReturnsTheDigitalItemsForTheGivenOrder(): void
    {
        /** @var ShopOrder $otherOrder */
        $otherOrder = $this->create(ShopOrder::class);

        $this->item($this->card(['title' => 'French Travel Card']));
        $this->item($this->card(['title' => 'German Travel Card']), $otherOrder);

        $this->assertCount(1, $this->items());
        $this->assertEquals('French Travel Card', $this->items()->first()->digital_card_title);
    }

    #[Test]
    public function itEagerLoadsTheCardAndItsRelations(): void
    {
        $this->item();

        $item = $this->items()->first();

        $this->assertTrue($item->relationLoaded('card'));
        $this->assertTrue($item->card->relationLoaded('price'));
        $this->assertTrue($item->card->relationLoaded('product'));
        $this->assertTrue($item->card->product->relationLoaded('travelCardSearchTerms'));
    }

    #[Test]
    public function itReturnsThePriceAssignedToTheCard(): void
    {
        $card = $this->card();

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->item($card);

        $this->assertSame(500, $this->items()->first()->card->price->price);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenTheCardDoesntHaveOne(): void
    {
        $this->item();

        $this->assertTrue($this->items()->first()->card->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itAssignsTheProductsCountriesToTheCard(): void
    {
        $this->item($this->card(['language' => 'french']));

        $this->attachTerm('france');

        $this->assertEquals(['france'], $this->items()->first()->card->countries->pluck('term')->all());
    }

    #[Test]
    public function itFlagsThePhysicalProductAsInTheBasketWhenTheOrderContainsTheCardsProduct(): void
    {
        $this->item();

        $this->build(ShopOrderItem::class)->inOrder($this->order)->inProduct($this->product)->create();

        $this->assertTrue($this->items()->first()->card->getAttribute('physicalProductInBasket'));
    }

    #[Test]
    public function itFlagsThePhysicalProductAsNotInTheBasketWhenTheOrderOnlyContainsOtherProducts(): void
    {
        $this->item();

        $this->build(ShopOrderItem::class)->inOrder($this->order)->create();

        $this->assertFalse($this->items()->first()->card->getAttribute('physicalProductInBasket'));
    }
}
