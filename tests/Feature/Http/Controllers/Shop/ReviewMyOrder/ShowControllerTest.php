<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\ReviewMyOrder;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopOrderReviewInvitation;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Carbon\Carbon;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopProduct $secondProduct;

    protected ShopProductVariant $secondVariant;

    protected ShopOrderItem $item;

    protected ShopOrderReviewInvitation $invitation;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        $this->withCategoriesAndProducts(1, 2, 2);

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
    }

    #[Test]
    public function itReturnsNotFoundForAnInvitationThatDoesntExist(): void
    {
        $this->get(route('shop.review-order', ['invitation' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itErrorsWhenVisitingTheReviewPageDirectly(): void
    {
        $this->get(route('shop.review-order', [$this->invitation]))->assertForbidden();
    }

    #[Test]
    public function itReturnsOkWhenVisitingAValidInvitation(): void
    {
        $this->getSignedLink()->assertOk();
    }

    #[Test]
    public function itLoadsTheInertiaPage(): void
    {
        $this->getSignedLink()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/ReviewMyOrder')
                    ->where('id', (string) $this->order->order_key)
                    ->where('invitation', $this->invitation->id)
                    ->where('name', $this->order->customer->name)
                    ->has('products', 1, fn (Assert $page) => $page->hasAll(['id', 'title', 'variants', 'image', 'link']))
            );
    }

    #[Test]
    public function itOnlyReturnsAProductOnceWhenTheOrderContainsMultipleVariantsOfIt(): void
    {
        $this->withMultipleVariantsInTheOrder();

        $this->getSignedLink()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('products', 2)
                    ->where('products.0.id', $this->product->id)
                    ->where('products.1.id', $this->secondProduct->id)
            );
    }

    #[Test]
    public function itReturnsEachVariantTitleTheCustomerOrderedAgainstTheProduct(): void
    {
        $this->withMultipleVariantsInTheOrder();

        $this->getSignedLink()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('products.0.variants', [$this->variant->title, $this->secondVariant->title])
                    ->where('products.1.variants', [$this->secondProduct->variants->first()->title])
            );
    }

    #[Test]
    public function itReturnsTheLiveProductTitleRatherThanTheOrderedVariantTitle(): void
    {
        $this->item->update(['product_title' => 'A stale variant specific title']);

        $this->getSignedLink()
            ->assertInertia(fn (Assert $page) => $page->where('products.0.title', $this->product->title));
    }

    #[Test]
    public function itStillReturnsAProductThatIsNoLongerSoldButWithoutALinkToIt(): void
    {
        $this->discontinueProduct();

        $this->getSignedLink()
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('products', 1)
                    ->where('products.0.id', $this->product->id)
                    ->where('products.0.title', $this->product->title)
                    ->where('products.0.link', null)
            );
    }

    #[Test]
    public function itStillReturnsTheVariantTitlesForAProductThatIsNoLongerSold(): void
    {
        $this->discontinueProduct();

        $this->getSignedLink()
            ->assertInertia(fn (Assert $page) => $page->where('products.0.variants', [$this->variant->title]));
    }

    #[Test]
    public function itRedirectsToTheThanksPageIfTheInvitationHasAReviewAssociatedWithIt(): void
    {
        $this->invitation->review()->create(['order_id' => $this->order->id, 'name' => 'Foo']);

        $this->getSignedLink()->assertRedirectToRoute('shop.review-order.thanks', $this->invitation);
    }

    protected function discontinueProduct(): void
    {
        ShopProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $this->product->id)
            ->update(['live' => false]);
    }

    protected function withMultipleVariantsInTheOrder(): void
    {
        $this->secondVariant = $this->product->variants->last();
        $this->secondProduct = ShopProduct::query()->where('id', '!=', $this->product->id)->firstOrFail();

        $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->secondVariant->id,
            'product_price' => 200,
        ]);

        $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->secondProduct->id,
            'product_variant_id' => $this->secondProduct->variants->first()->id,
            'product_price' => 200,
        ]);

        $this->order->load('items');
    }

    protected function getSignedLink(): TestResponse
    {
        return $this->get(resolve(UrlGenerator::class)->temporarySignedRoute(
            'shop.review-order',
            Carbon::now()->addMonths(6),
            [
                'invitation' => $this->invitation,
                'hash' => sha1($this->order->customer->email),
            ]
        ));
    }
}
