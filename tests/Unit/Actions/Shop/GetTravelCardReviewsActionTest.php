<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetTravelCardReviewsAction;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopIndexReviewResource;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTravelCardReviewsActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(2, 2, 1);

        /** @var ShopCategory $coeliacPlus */
        $coeliacPlus = $this->create(ShopCategory::class, ['id' => 11, 'title' => 'Coeliac+']);

        $coeliacPlus->products()->attach($this->product(3));
    }

    protected function product(int $index): ShopProduct
    {
        /** @var ShopProduct $product */
        $product = ShopProduct::query()->orderBy('id')->get()->get($index);

        return $product;
    }

    protected function review(ShopProduct $product, int $rating, ?string $body = 'A review'): void
    {
        /** @var ShopOrderReview $review */
        $review = $this->create(ShopOrderReview::class);

        $this->build(ShopOrderReviewItem::class)
            ->forReview($review)
            ->forProduct($product)
            ->create(['rating' => $rating, 'review' => $body]);
    }

    /** @return array{reviews: mixed, count: int, average: float|null} */
    protected function summary(): array
    {
        return $this->callAction(GetTravelCardReviewsAction::class);
    }

    #[Test]
    public function itReturnsReviewResourcesForTravelCardProducts(): void
    {
        $this->review($this->product(0), 5);

        $summary = $this->summary();

        $this->assertCount(1, $summary['reviews']->collection);
        $this->assertInstanceOf(ShopIndexReviewResource::class, $summary['reviews']->collection->first());
    }

    #[Test]
    public function itIgnoresReviewsForProductsOutsideTheTravelCardCategories(): void
    {
        $this->review($this->product(2), 5);

        $this->assertCount(0, $this->summary()['reviews']->collection);
        $this->assertEquals(0, $this->summary()['count']);
    }

    #[Test]
    public function itIncludesTheCoeliacPlusCategory(): void
    {
        $this->review($this->product(3), 5);

        $this->assertEquals(1, $this->summary()['count']);
    }

    #[Test]
    public function itDoesNotListReviewsRatedBelowFour(): void
    {
        $this->review($this->product(0), 3);

        $this->assertCount(0, $this->summary()['reviews']->collection);
    }

    #[Test]
    public function itDoesNotListReviewsWithNoWrittenFeedback(): void
    {
        $this->review($this->product(0), 5, null);
        $this->review($this->product(1), 5, '');

        $this->assertCount(0, $this->summary()['reviews']->collection);
    }

    #[Test]
    public function itListsAtMostSixReviews(): void
    {
        foreach (range(1, 8) as $index) {
            $this->review($this->product(0), 5, "Review {$index}");
        }

        $this->assertCount(6, $this->summary()['reviews']->collection);
    }

    #[Test]
    public function itCountsEveryRatingIncludingThoseWithoutFeedback(): void
    {
        $this->review($this->product(0), 5);
        $this->review($this->product(0), 2, null);
        $this->review($this->product(1), 3, '');

        $this->assertEquals(3, $this->summary()['count']);
    }

    #[Test]
    public function itAveragesTheRatingToASingleDecimalPlace(): void
    {
        $this->review($this->product(0), 5);
        $this->review($this->product(0), 5);
        $this->review($this->product(1), 4);

        $this->assertEquals(4.7, $this->summary()['average']);
    }

    #[Test]
    public function itReturnsANullAverageWhenThereAreNoReviews(): void
    {
        $summary = $this->summary();

        $this->assertNull($summary['average']);
        $this->assertEquals(0, $summary['count']);
        $this->assertCount(0, $summary['reviews']->collection);
    }

    #[Test]
    public function itCachesTheSummary(): void
    {
        $this->review($this->product(0), 5);

        $this->assertFalse(Cache::has('travel-card-reviews'));

        $summary = $this->summary();

        $this->assertTrue(Cache::has('travel-card-reviews'));
        $this->assertEquals($summary, Cache::get('travel-card-reviews'));
    }
}
