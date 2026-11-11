<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetOrderItemsActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create();
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        app(AddProductToBasketAction::class)->handle($this->order, $this->product, $this->variant, 1);
    }

    protected function items(bool $includeDigitalBundles = true, bool $includeDigitalUpsell = false): AnonymousResourceCollection
    {
        return $this->callAction(GetOrderItemsAction::class, $this->order, $includeDigitalBundles, $includeDigitalUpsell);
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

    protected function bundle(?ShopDigitalCard $card = null): ShopOrderDigitalItem
    {
        /** @var ShopOrderItem $orderItem */
        $orderItem = $this->order->items()->first();

        /** @var ShopOrderDigitalItem $bundle */
        $bundle = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->forCard($card ?? $this->card())
            ->create(['physical_order_item_id' => $orderItem->id]);

        return $bundle;
    }

    protected function attachTerm(string $term, string $language = 'french'): void
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => 'country']);

        $this->product->travelCardSearchTerms()->attach($searchTerm, [
            'card_language' => $language,
            'card_score' => 10,
            'card_show_on_product_page' => true,
        ]);
    }

    #[Test]
    public function itReturnsACollectionOfOrderItems(): void
    {
        $items = $this->items();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $items);
        $this->assertInstanceOf(ShopOrderItemResource::class, $items->first());
    }

    #[Test]
    public function itLoadsAndFormatsTheDigitalBundlesByDefault(): void
    {
        $this->bundle();

        $item = $this->items()->first();

        $this->assertTrue($item->relationLoaded('digitalBundles'));
        $this->assertCount(1, $item->digitalBundles);

        $card = $item->digitalBundles->first()->card;

        $this->assertTrue($card->price->is(ShopDigitalCard::defaultPrice()));
        $this->assertTrue($card->relationLoaded('product'));
    }

    #[Test]
    public function itFlagsTheDigitalBundleCardsAsBeingInTheBasket(): void
    {
        $this->bundle();

        $card = $this->items()->first()->digitalBundles->first()->card;

        $this->assertTrue($card->getAttribute('isInBasket'));
    }

    #[Test]
    public function itDoesntLoadTheDigitalBundlesWhenTheyreExcluded(): void
    {
        $this->bundle();

        $this->assertFalse($this->items(includeDigitalBundles: false)->first()->relationLoaded('digitalBundles'));
    }

    #[Test]
    public function itDoesntLoadTheProductsDigitalCardsByDefault(): void
    {
        $this->card();

        $this->assertFalse($this->items()->first()->product->relationLoaded('digitalCards'));
    }

    #[Test]
    public function itLoadsTheProductsDigitalCardsAndTheirPricesForTheUpsell(): void
    {
        $card = $this->card();

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price_with_physical_product' => 500]);

        $product = $this->items(includeDigitalUpsell: true)->first()->product;

        $this->assertTrue($product->relationLoaded('digitalCards'));
        $this->assertCount(1, $product->digitalCards);
        $this->assertSame(500, $product->digitalCards->first()->price->price_with_physical_product);
    }

    #[Test]
    public function itAssignsTheItemsProductToEachUpsellCard(): void
    {
        $this->card();

        $card = $this->items(includeDigitalUpsell: true)->first()->product->digitalCards->first();

        $this->assertTrue($card->relationLoaded('product'));
        $this->assertTrue($card->product->is($this->product));
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceOnUpsellCardsWithoutOne(): void
    {
        $this->card();

        $card = $this->items(includeDigitalUpsell: true)->first()->product->digitalCards->first();

        $this->assertTrue($card->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itAssignsTheProductsCountriesToTheUpsellCards(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('france');

        $card = $this->items(includeDigitalUpsell: true)->first()->product->digitalCards->first();

        $this->assertEquals(['france'], $card->countries->pluck('term')->all());
    }

    #[Test]
    public function itFlagsAnUpsellCardAsBeingInTheBasketWhenTheBasketAlreadyContainsIt(): void
    {
        $card = $this->card();

        $this->bundle($card);

        $upsellCard = $this->items(includeDigitalUpsell: true)->first()->product->digitalCards->first();

        $this->assertTrue($upsellCard->getAttribute('isInBasket'));
    }

    #[Test]
    public function itFlagsAnUpsellCardAsNotBeingInTheBasketWhenTheBasketDoesntContainIt(): void
    {
        $this->card();

        $upsellCard = $this->items(includeDigitalUpsell: true)->first()->product->digitalCards->first();

        $this->assertFalse($upsellCard->getAttribute('isInBasket'));
    }
}
