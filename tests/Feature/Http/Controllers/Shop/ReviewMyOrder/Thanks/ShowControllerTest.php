<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\ReviewMyOrder\Thanks;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopOrderReviewInvitation;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopOrderReviewInvitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        $this->withCategoriesAndProducts(1, 1);

        $this->order = $this->build(ShopOrder::class)
            ->asShipped()
            ->create();

        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_price' => 200,
        ]);

        $this->invitation = $this->order->reviewInvitation()->create();

        $this->invitation->review()->create([
            'name' => 'Foo',
        ]);
    }

    #[Test]
    public function itReturnsNotFoundForAnInvitationThatDoesntExist(): void
    {
        $this->get(route('shop.review-order', ['invitation' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkWhenVisitingAValidInvitation(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itLoadsTheInertiaPage(): void
    {
        $this->makeRequest()
            ->assertInertia(fn (Assert $page) => $page->component('Shop/ReviewMyOrderThanks'));
    }

    #[Test]
    public function itReturns404IfTheInvitationDoesntHaveAReview(): void
    {
        $this->invitation->review()->delete();

        $this->makeRequest()->assertNotFound();
    }

    protected function makeRequest(): TestResponse
    {
        return $this->get(route('shop.review-order.thanks', $this->invitation));
    }
}
