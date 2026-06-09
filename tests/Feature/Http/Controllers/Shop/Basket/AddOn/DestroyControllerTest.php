<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket\AddOn;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class DestroyControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopProductAddOn $addOn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create();
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();
        $this->addOn = $this->create(ShopProductAddOn::class, ['product_id' => $this->product->id]);

        $this->item = $this->build(ShopOrderItem::class)
            ->withAddOn($this->addOn)
            ->create([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'product_variant_id' => $this->variant->id,
            ]);
    }

    #[Test]
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class);

        $this->makeRequest();
    }

    #[Test]
    public function itReturnsNotFoundIfABasketDoesntExist(): void
    {
        $this->order->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheItemIsntInTheUsersBasket(): void
    {
        $item = $this->create(ShopOrderItem::class);

        $this->makeRequest($item->id)->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheItemDoesntHaveAnAddOn(): void
    {
        $this->item->update([
            'product_add_on_id' => null,
            'product_add_on_title' => null,
            'product_add_on_price' => null,
        ]);

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductDoesntHaveAnAddOn(): void
    {
        $this->addOn->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductAddOnDoesntMatchTheItemAddOn(): void
    {
        $differentAddOn = $this->create(ShopProductAddOn::class);

        $this->item->update([
            'product_add_on_id' => $differentAddOn->id,
        ]);

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itRemovesTheAddOnFromTheOrderItem(): void
    {
        $this->makeRequest();

        $this->item->refresh();

        $this->assertNull($this->item->product_add_on_id);
        $this->assertNull($this->item->product_add_on_title);
        $this->assertNull($this->item->product_add_on_price);
    }

    #[Test]
    public function itDoesNotDeleteTheOrderItem(): void
    {
        $this->makeRequest();

        $this->assertModelExists($this->item);
    }

    #[Test]
    public function itTouchesTheOrderUpdateTimestamp(): void
    {
        TestTime::addMinutes(30);

        $this->makeRequest();

        $this->assertTrue($this->order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itRedirectsBack(): void
    {
        $this
            ->from(route('shop.product', ['product' => $this->product->slug]))
            ->makeRequest()
            ->assertRedirectToRoute('shop.product', ['product' => $this->product->slug]);
    }

    protected function makeRequest(?int $item = null): TestResponse
    {
        return $this->withCookie('basket_token', $this->order->token)
            ->delete(route('shop.basket.add-on.remove', ['item' => $item ?? $this->item->id]));
    }
}
