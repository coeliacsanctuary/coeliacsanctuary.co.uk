<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\ReviewMyOrder;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewInvitation;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\ShopSource;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\RequestFactories\ReviewMyOrderRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopOrderItem $item;

    protected ShopOrderReviewInvitation $invitation;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        $this->withCategoriesAndProducts(1, 1, 2);

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
        $this->post(route('shop.review-order.store', ['invitation' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheInvitationHasAReviewAssociatedWithIt(): void
    {
        $this->invitation->review()->create(['order_id' => $this->order->id, 'name' => 'Foo']);

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itAllowsRequestsWithoutAName(): void
    {
        $this->makeRequest(['name' => null])->assertSessionDoesntHaveErrors('name');
    }

    #[Test]
    public function itErrorsIfARequestHasAnInvalidName(): void
    {
        $this->makeRequest(['name' => 123])->assertSessionHasErrors('name');
    }

    #[Test]
    public function itAllowsRequestsWithoutWhereHeard(): void
    {
        $this->makeRequest(['whereHeard' => null])->assertSessionHasErrors('whereHeard');
    }

    #[Test]
    public function itErrorsWhenWhereHeardIsntAnArray(): void
    {
        $this->makeRequest(['whereHeard' => 123])->assertSessionHasErrors('whereHeard');
    }

    #[Test]
    public function itErrorsWhenWhereHeardIsntAnArrayOfStrings(): void
    {
        $this->makeRequest(['whereHeard' => ['foo', 123]])
            ->assertSessionDoesntHaveErrors('whereHeard.0')
            ->assertSessionHasErrors('whereHeard.1');
    }

    #[Test]
    public function itErrorsWithoutAProductsParameterInTheRequest(): void
    {
        $this->makeRequest(products: collect([]))->assertSessionHasErrors('products');
    }

    #[Test]
    public function itErrorsIfTheProductsParameterIsntAnArray(): void
    {
        $this->makeRequest(['products' => 'foo'])->assertSessionHasErrors('products');
    }

    #[Test]
    public function itErrorsIfAProductDoesntHaveAnId(): void
    {
        $this->makeRequest(['products' => [['review' => 'foo']]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfAProductDoesntHasAnInvalidId(): void
    {
        $this->makeRequest(['products' => [['id' => 'foo']]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfAProductHasAIdThatDoesntExist(): void
    {
        $this->makeRequest(['products' => [['id' => 999]]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfTryingToLeaveFeedbackForAnItemNotInTheOrder(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->makeRequest(['products' => [['id' => $product->id]]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfAProductDoesntHaveARating(): void
    {
        $this->makeRequest(['products' => [['id' => 1]]])->assertSessionHasErrors('products.0.rating');
    }

    #[Test]
    public function itErrorsIfAProductHasARatingThatIsntNumeric(): void
    {
        $this->makeRequest(['products' => [['rating' => 'foo']]])->assertSessionHasErrors('products.0.rating');
    }

    #[Test]
    public function itErrorsIfAProductsHasARatingThatIsntInTheRange(): void
    {
        $this->makeRequest(['products' => [['rating' => -1]]])->assertSessionHasErrors('products.0.rating');

        $this->makeRequest(['products' => [['rating' => 6]]])->assertSessionHasErrors('products.0.rating');
    }

    #[Test]
    public function itErrorsIfAProductHasAnInvalidReview(): void
    {
        $this->makeRequest(['products' => [['review' => 123]]])->assertSessionHasErrors('products.0.review');
    }

    #[Test]
    public function itErrorsIfTheSameProductIsSubmittedTwice(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => 'foo'],
            ['id' => $this->product->id, 'rating' => 4, 'review' => 'bar'],
        ]])->assertSessionHasErrors('products.1.id');
    }

    #[Test]
    public function itOnlyCreatesOneReviewItemPerProductForAnOrderWithMultipleVariants(): void
    {
        $secondVariant = $this->product->variants->last();

        $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $secondVariant->id,
            'product_price' => 200,
        ]);

        $this->makeRequest(products: $this->order->refresh()->items);

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 1);
    }

    #[Test]
    public function itErrorsIfTheNameIsTooLong(): void
    {
        $this->makeRequest(['name' => str_repeat('a', 256)])->assertSessionHasErrors('name');
    }

    #[Test]
    public function itErrorsIfAReviewIsTooLong(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => str_repeat('a', 1001)],
        ]])->assertSessionHasErrors('products.0.review');
    }

    #[Test]
    public function itErrorsIfAWhereHeardValueIsTooLong(): void
    {
        $this->makeRequest(['whereHeard' => [str_repeat('a', 256)]])->assertSessionHasErrors('whereHeard.0');
    }

    #[Test]
    public function itAssociatesTheShopSourceWithTheOrder(): void
    {
        $this->assertEmpty($this->order->sources);

        $this->makeRequest();

        $this->assertNotEmpty($this->order->refresh()->sources);
    }

    #[Test]
    public function itCreatesTheShopSourceIfItDoestExist(): void
    {
        $this->assertDatabaseMissing(ShopSource::class, ['source' => 'foobar']);

        $this->makeRequest(['whereHeard' => ['foobar']]);

        $this->assertDatabaseHas(ShopSource::class, ['source' => 'foobar']);
    }

    #[Test]
    public function itCreatesTheReview(): void
    {
        $this->assertDatabaseEmpty(ShopOrderReview::class);

        $this->makeRequest();
        $this->assertDatabaseCount(ShopOrderReview::class, 1);
        $this->assertInstanceOf(ShopOrderReview::class, $this->invitation->review);
    }

    #[Test]
    public function itCreatesAReviewForTheProducts(): void
    {
        $this->assertDatabaseEmpty(ShopOrderReviewItem::class);

        $this->makeRequest();

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 1);
        $this->assertDatabaseHas(ShopOrderReviewItem::class, [
            'product_id' => $this->product->id,
            'order_id' => $this->order->id,
        ]);
    }

    #[Test]
    public function itRedirectsToTheThanksPage(): void
    {
        $this->makeRequest()->assertRedirectToRoute('shop.review-order.thanks', $this->invitation);
    }

    protected function makeRequest(array $params = [], ?Collection $products = null): TestResponse
    {
        $products ??= collect([$this->item]);
        $data = ReviewMyOrderRequestFactory::new($params);

        if ( ! array_key_exists('products', $params)) {
            $data = $data->forProducts($products);
        }

        return $this->post(route('shop.review-order.store', $this->invitation), $data->create($params));
    }
}
