<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetBestSellingProductsForShopIndexAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Resources\Shop\ShopPopularProductResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetBestSellingProductsForShopIndexActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 3);
    }

    #[Test]
    public function itReturnsACollectionOfProductResources(): void
    {
        $this->sell($this->product(0), 5);

        $products = $this->callAction(GetBestSellingProductsForShopIndexAction::class);

        $this->assertInstanceOf(AnonymousResourceCollection::class, $products);

        $products->each(function ($product): void {
            $this->assertInstanceOf(ShopPopularProductResource::class, $product);
        });
    }

    #[Test]
    public function itReturnsNothingWhenThereAreNoOrders(): void
    {
        $this->assertCount(0, $this->callAction(GetBestSellingProductsForShopIndexAction::class));
    }

    #[Test]
    public function itOrdersTheProductsByTheNumberSold(): void
    {
        $this->sell($this->product(0), 2);
        $this->sell($this->product(1), 9);
        $this->sell($this->product(2), 5);

        $this->assertSame(
            [$this->product(1)->title, $this->product(2)->title, $this->product(0)->title],
            $this->titles(),
        );
    }

    #[Test]
    public function itSumsTheQuantityAcrossMultipleOrders(): void
    {
        $this->sell($this->product(0), 4);
        $this->sell($this->product(0), 4);
        $this->sell($this->product(1), 7);

        $this->assertSame([$this->product(0)->title, $this->product(1)->title], $this->titles());
    }

    #[Test]
    public function itOnlyConsidersOrdersThatHaveBeenPaidReadyOrShipped(): void
    {
        $this->sell($this->product(0), 5, state: 'asBasket');
        $this->sell($this->product(1), 5, state: 'asPending');
        $this->sell($this->product(2), 1, state: 'asPaid');

        $this->assertSame([$this->product(2)->title], $this->titles());
    }

    #[Test]
    public function itDoesntConsiderOrdersOutsideOfTheConfiguredWindow(): void
    {
        config(['coeliac.shop.popular_products_window_days' => 7]);

        $this->sell($this->product(0), 20, placedAt: Carbon::now()->subDays(30));
        $this->sell($this->product(1), 1);

        $this->assertSame([$this->product(1)->title], $this->titles());
    }

    #[Test]
    public function itDoesntReturnProductsThatAreOutOfStock(): void
    {
        $this->sell($this->product(0), 20);
        $this->sell($this->product(1), 1);

        ShopProductVariant::query()
            ->where('product_id', $this->product(0)->id)
            ->update(['quantity' => 0]);

        $this->assertSame([$this->product(1)->title], $this->titles());
    }

    #[Test]
    public function itDoesntReturnProductsWithoutALiveVariant(): void
    {
        $soldOut = $this->product(0);
        $expected = $this->product(1);

        $this->sell($soldOut, 20);
        $this->sell($expected, 1);

        ShopProductVariant::query()
            ->where('product_id', $soldOut->id)
            ->update(['live' => false]);

        $this->assertSame([$expected->title], $this->titles());
    }

    #[Test]
    public function itCachesTheProducts(): void
    {
        $this->sell($this->product(0), 5);

        $this->assertFalse(Cache::has('shop-popular-products'));

        $products = $this->callAction(GetBestSellingProductsForShopIndexAction::class);

        $this->assertTrue(Cache::has('shop-popular-products'));
        $this->assertSame($products, Cache::get('shop-popular-products'));
    }

    protected function product(int $index): ShopProduct
    {
        return ShopProduct::query()->orderBy('id')->get()->get($index);
    }

    protected function sell(ShopProduct $product, int $quantity, string $state = 'asPaid', ?Carbon $placedAt = null): void
    {
        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->{$state}()->create([
            'created_at' => $placedAt ?? Carbon::now(),
        ]);

        $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->inProduct($product)
            ->create(['quantity' => $quantity]);
    }

    /** @return string[] */
    protected function titles(): array
    {
        return $this->callAction(GetBestSellingProductsForShopIndexAction::class)
            ->map(fn (ShopPopularProductResource $product) => $product->title)
            ->values()
            ->toArray();
    }
}
