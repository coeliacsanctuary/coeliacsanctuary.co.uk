<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\ReviewMyOrder;

use App\Models\SealiacOverview;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
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
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\ReviewMyOrderRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopOrderItem $item;

    protected ShopOrderReviewInvitation $invitation;

    protected function setUp(): void
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
    public function itErrorsIfTheNameIsTooLong(): void
    {
        $this->makeRequest(['name' => str_repeat('a', 256)])->assertSessionHasErrors('name');
    }

    #[Test]
    public function itAllowsRequestsWithoutWhereHeard(): void
    {
        $this->makeRequest(without: ['whereHeard'])->assertSessionDoesntHaveErrors('whereHeard');
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
    public function itErrorsIfAWhereHeardValueIsTooLong(): void
    {
        $this->makeRequest(['whereHeard' => [str_repeat('a', 256)]])->assertSessionHasErrors('whereHeard.0');
    }

    #[Test]
    public function itErrorsWithoutAProductsParameterInTheRequest(): void
    {
        $this->makeRequest(items: collect([]))->assertSessionHasErrors('products');
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
    public function itErrorsIfAProductHasAnInvalidId(): void
    {
        $this->makeRequest(['products' => [['id' => 'foo']]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfTheSameProductIsSubmittedTwice(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => 'foo', 'type' => 'physical'],
            ['id' => $this->product->id, 'rating' => 4, 'review' => 'bar', 'type' => 'physical'],
        ]])->assertSessionHasErrors('products.1.id');
    }

    #[Test]
    public function itAllowsAProductAndADigitalCardThatShareAnId(): void
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create(['id' => $this->product->id]);

        $digitalItem = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->forCard($card)
            ->create();

        $this->makeRequest(items: collect([$this->item, $digitalItem]))->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 2);
    }

    #[Test]
    public function itErrorsIfTryingToLeaveFeedbackForAProductNotInTheOrder(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->makeRequest(['products' => [['id' => $product->id, 'type' => 'physical']]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfTryingToLeaveFeedbackForADigitalCardNotInTheOrder(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->makeRequest(['products' => [['id' => $card->id, 'type' => 'digital']]])->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfTryingToLeaveFeedbackForADigitalCardAsAPhysicalProduct(): void
    {
        $digitalItem = $this->digitalItem();

        $this->makeRequest(['products' => [['id' => $digitalItem->digital_card_id, 'type' => 'physical']]])
            ->assertSessionHasErrors('products.0.id');
    }

    #[Test]
    public function itErrorsIfAProductDoesntHaveARating(): void
    {
        $this->makeRequest(['products' => [['id' => $this->product->id, 'type' => 'physical']]])->assertSessionHasErrors('products.0.rating');
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
    public function itErrorsIfAReviewIsTooLong(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => str_repeat('a', 1001), 'type' => 'physical'],
        ]])->assertSessionHasErrors('products.0.review');
    }

    #[Test]
    public function itErrorsIfAProductDoesntHaveAType(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => 'foo'],
        ]])->assertSessionHasErrors('products.0.type');
    }

    #[Test]
    public function itErrorsIfAProductHasAnInvalidType(): void
    {
        $this->makeRequest(['products' => [
            ['id' => $this->product->id, 'rating' => 5, 'review' => 'foo', 'type' => 'foo'],
        ]])->assertSessionHasErrors('products.0.type');
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
    public function itDoesntDuplicateAnExistingShopSource(): void
    {
        $source = $this->create(ShopSource::class, ['source' => 'facebook']);

        $this->makeRequest();

        $this->assertDatabaseCount(ShopSource::class, 2);
        $this->assertTrue($this->order->refresh()->sources->contains($source));
    }

    #[Test]
    public function itCreatesTheReview(): void
    {
        $this->assertDatabaseEmpty(ShopOrderReview::class);

        $this->makeRequest(['name' => 'Jamie']);

        $this->assertDatabaseCount(ShopOrderReview::class, 1);

        /** @var ShopOrderReview $review */
        $review = $this->invitation->refresh()->review;

        $this->assertInstanceOf(ShopOrderReview::class, $review);
        $this->assertEquals('Jamie', $review->name);
        $this->assertEquals($this->order->id, $review->order_id);
    }

    #[Test]
    public function itCreatesAReviewForTheProducts(): void
    {
        $this->assertDatabaseEmpty(ShopOrderReviewItem::class);

        $this->makeRequest();

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 1);

        /** @var ShopOrderReviewItem $reviewItem */
        $reviewItem = ShopOrderReviewItem::query()->sole();

        $this->assertEquals($this->product->id, $reviewItem->product_id);
        $this->assertEquals(ShopProduct::class, $reviewItem->product_type);
        $this->assertEquals($this->order->id, $reviewItem->order_id);
    }

    #[Test]
    public function itCreatesAReviewForADigitalCard(): void
    {
        $digitalItem = $this->digitalItem();

        $this->makeRequest(items: collect([$digitalItem]));

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 1);

        /** @var ShopOrderReviewItem $reviewItem */
        $reviewItem = ShopOrderReviewItem::query()->sole();

        $this->assertEquals($digitalItem->digital_card_id, $reviewItem->product_id);
        $this->assertEquals(ShopDigitalCard::class, $reviewItem->product_type);
        $this->assertEquals($this->order->id, $reviewItem->order_id);
    }

    #[Test]
    public function itCreatesAReviewForProductsAndDigitalCardsInTheSameOrder(): void
    {
        $digitalItem = $this->digitalItem();

        $this->makeRequest(items: collect([$this->item, $digitalItem]));

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 2);

        $this->assertEqualsCanonicalizing(
            [ShopProduct::class, ShopDigitalCard::class],
            ShopOrderReviewItem::query()->pluck('product_type')->all(),
        );
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

        $this->makeRequest(items: $this->order->refresh()->items);

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 1);
    }

    #[Test]
    public function itInvalidatesTheSealiacOverviewForAReviewedProduct(): void
    {
        /** @var SealiacOverview $overview */
        $overview = $this->build(SealiacOverview::class)->forProduct($this->product)->create();

        $this->makeRequest();

        $this->assertTrue($overview->refresh()->invalidated);
    }

    #[Test]
    public function itDoesntInvalidateASealiacOverviewForADigitalCard(): void
    {
        $digitalItem = $this->digitalItem();

        /** @var SealiacOverview $overview */
        $overview = $this->build(SealiacOverview::class)->create([
            'model_type' => ShopProduct::class,
            'model_id' => $digitalItem->digital_card_id,
        ]);

        $this->makeRequest(items: collect([$digitalItem]));

        $this->assertFalse($overview->refresh()->invalidated);
    }

    #[Test]
    public function itRedirectsToTheThanksPage(): void
    {
        $this->makeRequest()->assertRedirectToRoute('shop.review-order.thanks', $this->invitation);
    }

    protected function digitalItem(): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $digitalItem */
        $digitalItem = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->create();

        return $digitalItem;
    }

    /** @param Collection<int, ShopOrderItem | ShopOrderDigitalItem>|null $items */
    protected function makeRequest(array $params = [], ?Collection $items = null, array $without = []): TestResponse
    {
        $items ??= collect([$this->item]);
        $data = ReviewMyOrderRequestFactory::new($params);

        if ( ! array_key_exists('products', $params)) {
            $data = $data->forItems($items);
        }

        if ($without !== []) {
            $data = $data->without($without);
        }

        return $this->post(route('shop.review-order.store', $this->invitation), $data->create($params));
    }
}
