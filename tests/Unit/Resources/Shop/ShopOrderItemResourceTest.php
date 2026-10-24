<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOrderItemResourceTest extends TestCase
{
    protected ShopOrderItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        /** @var ShopOrder $order */
        $order = ShopOrder::query()->create();

        /** @var ShopProduct $product */
        $product = ShopProduct::query()->firstOrFail();

        app(AddProductToBasketAction::class)->handle($order, $product, $product->variants->first(), 1);

        /** @var ShopOrderItem $item */
        $item = $order->items()->firstOrFail();

        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->forProduct($product)->create();

        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->forCard($card)
            ->create(['physical_order_item_id' => $item->id]);

        $this->item = $item->load('digitalBundles');
    }

    protected function resource(): array
    {
        return (new ShopOrderItemResource($this->item))->toArray(new Request());
    }

    #[Test]
    public function itReturnsTheDigitalBundlesWhenDigitalCardsAreEnabled(): void
    {
        config(['coeliac.digital_cards_enabled' => true]);

        $bundles = $this->resource()['digitalBundles'];

        $this->assertInstanceOf(AnonymousResourceCollection::class, $bundles);
        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(ShopOrderDigitalItemResource::class, $bundles->first());
    }

    #[Test]
    public function itReturnsNullForTheDigitalBundlesWhenDigitalCardsAreDisabled(): void
    {
        config(['coeliac.digital_cards_enabled' => false]);

        $this->assertNull($this->resource()['digitalBundles']);
    }
}
