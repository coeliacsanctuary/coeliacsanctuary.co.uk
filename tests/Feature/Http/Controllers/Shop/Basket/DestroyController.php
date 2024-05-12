<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Testing\TestResponse;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class DestroyController extends TestCase
{
    protected ShopOrder $order;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create();
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
        ]);
    }

    /** @test */
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class);

        $this->makeRequest();
    }

    /** @test */
    public function itReturnsNotFoundIfABasketDoesntExist(): void
    {
        $this->order->delete();

        $this->makeRequest()->assertNotFound();
    }

    /** @test */
    public function itReturnsNotFoundIfTheItemIsntInTheUsersBasket(): void
    {
        $item = $this->create(ShopOrderItem::class);

        $this->makeRequest($item->id)->assertNotFound();
    }

    /** @test */
    public function itRemovesTheShopOrderItemRow(): void
    {
        $this->makeRequest();

        $this->assertModelMissing($this->item);
    }

    /** @test */
    public function itIncreasesTheProductQuantity(): void
    {
        $quantity = $this->variant->quantity;

        $this->makeRequest();

        $this->assertEquals($quantity + 1, $this->variant->refresh()->quantity);
    }

    /** @test */
    public function itTouchesTheOrderUpdateTimestamp(): void
    {
        TestTime::addMinutes(30);

        $this->makeRequest();

        $this->assertTrue($this->order->refresh()->updated_at->isSameSecond(now()));
    }

    /** @test */
    public function itItRedirectsBack(): void
    {
        $this
            ->from(route('shop.product', ['product' => $this->product->slug]))
            ->makeRequest()
            ->assertRedirectToRoute('shop.product', ['product' => $this->product->slug]);
    }

    protected function makeRequest(?int $item = null): TestResponse
    {
        return $this->withCookie('basket_token', $this->order->token)
            ->delete(route('shop.basket.remove', ['item' => $item ?? $this->item->id]));
    }
}
