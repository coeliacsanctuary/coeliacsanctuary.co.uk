<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\DigitalCards\Reviews;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected ShopDigitalCard $digitalCard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->digitalCard = $this->create(ShopDigitalCard::class);
    }

    protected function createReview(array $attributes = []): ShopOrderReviewItem
    {
        /** @var ShopOrderReviewItem $review */
        $review = $this->build(ShopOrderReviewItem::class)
            ->forDigitalCard($this->digitalCard)
            ->state($attributes)
            ->create();

        return $review;
    }

    protected function makeRequest(array $parameters = []): TestResponse
    {
        return $this->getJson(route('api.shop.digital-cards.reviews.index', array_merge(
            ['digitalCard' => $this->digitalCard->id],
            $parameters,
        )));
    }

    #[Test]
    public function itReturnsNotFoundIfTheDigitalCardDoesntExist(): void
    {
        $this->getJson(route('api.shop.digital-cards.reviews.index', ['digitalCard' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsTheAverageRatingRoundedToTheNearestHalf(): void
    {
        $this->createReview(['rating' => 5]);
        $this->createReview(['rating' => 4]);
        $this->createReview(['rating' => 4]);

        $this->makeRequest()->assertJsonPath('data.rating.average', 4.5);
    }

    #[Test]
    public function itReturnsTheNumberOfReviews(): void
    {
        $this->createReview();
        $this->createReview();

        $this->makeRequest()->assertJsonPath('data.rating.count', 2);
    }

    #[Test]
    public function itReturnsTheNumberOfReviewsForEachRatingFromFiveDownToOne(): void
    {
        $this->createReview(['rating' => 5]);
        $this->createReview(['rating' => 5]);
        $this->createReview(['rating' => 3]);

        $this->makeRequest()->assertJsonPath('data.rating.breakdown', [
            ['rating' => 5, 'count' => 2],
            ['rating' => 4, 'count' => 0],
            ['rating' => 3, 'count' => 1],
            ['rating' => 2, 'count' => 0],
            ['rating' => 1, 'count' => 0],
        ]);
    }

    #[Test]
    public function itReturnsTheReviewsWithTheNameOfThePersonWhoLeftThem(): void
    {
        /** @var ShopOrderReview $parent */
        $parent = $this->create(ShopOrderReview::class, ['name' => 'Jamie']);

        $this->createReview(['review_id' => $parent->id, 'review' => 'A great card', 'rating' => 4]);

        $this->makeRequest()
            ->assertJsonCount(1, 'data.reviews.data')
            ->assertJsonPath('data.reviews.data.0.name', 'Jamie')
            ->assertJsonPath('data.reviews.data.0.review', 'A great card')
            ->assertJsonPath('data.reviews.data.0.rating', 4);
    }

    #[Test]
    public function itDoesntReturnReviewsLeftForAnotherDigitalCard(): void
    {
        /** @var ShopDigitalCard $otherCard */
        $otherCard = $this->create(ShopDigitalCard::class);

        $this->build(ShopOrderReviewItem::class)->forDigitalCard($otherCard)->create();

        $this->makeRequest()
            ->assertJsonPath('data.rating.count', 0)
            ->assertJsonCount(0, 'data.reviews.data');
    }

    #[Test]
    public function itDoesntReturnReviewsLeftForAProductWithTheSameId(): void
    {
        $digitalCardId = $this->digitalCard->id;

        $this->build(ShopOrderReviewItem::class)
            ->state(fn () => ['product_type' => ShopProduct::class, 'product_id' => $digitalCardId])
            ->create();

        $this->makeRequest()
            ->assertJsonPath('data.rating.count', 0)
            ->assertJsonCount(0, 'data.reviews.data');
    }

    #[Test]
    public function itReturnsTheNewestReviewsFirst(): void
    {
        $this->createReview(['review' => 'Oldest', 'created_at' => now()->subWeek()]);
        $this->createReview(['review' => 'Newest', 'created_at' => now()]);
        $this->createReview(['review' => 'Middle', 'created_at' => now()->subDay()]);

        $this->makeRequest()
            ->assertJsonPath('data.reviews.data.0.review', 'Newest')
            ->assertJsonPath('data.reviews.data.1.review', 'Middle')
            ->assertJsonPath('data.reviews.data.2.review', 'Oldest');
    }

    #[Test]
    public function itReturnsSevenReviewsPerPage(): void
    {
        foreach (range(1, 8) as $index) {
            $this->createReview();
        }

        $this->makeRequest()
            ->assertJsonCount(7, 'data.reviews.data')
            ->assertJsonPath('data.reviews.total', 8);
    }

    #[Test]
    public function itReturnsTheSecondPageOfReviews(): void
    {
        foreach (range(1, 8) as $index) {
            $this->createReview();
        }

        $this->makeRequest(['page' => 2])
            ->assertJsonCount(1, 'data.reviews.data')
            ->assertJsonPath('data.reviews.current_page', 2);
    }

    #[Test]
    public function itFiltersTheReviewsToTheGivenRating(): void
    {
        $this->createReview(['rating' => 5, 'review' => 'Five stars']);
        $this->createReview(['rating' => 3, 'review' => 'Three stars']);

        $this->makeRequest(['reviewFilter' => 5])
            ->assertJsonCount(1, 'data.reviews.data')
            ->assertJsonPath('data.reviews.data.0.review', 'Five stars');
    }

    #[Test]
    public function itStillReturnsTheRatingBreakdownForAllReviewsWhenFiltering(): void
    {
        $this->createReview(['rating' => 5]);
        $this->createReview(['rating' => 3]);

        $this->makeRequest(['reviewFilter' => 5])
            ->assertJsonPath('data.rating.count', 2)
            ->assertJsonPath('data.rating.breakdown.0', ['rating' => 5, 'count' => 1])
            ->assertJsonPath('data.rating.breakdown.2', ['rating' => 3, 'count' => 1]);
    }

    #[Test]
    public function itReturnsAllOfTheReviewsWhenNoRatingFilterIsGiven(): void
    {
        $this->createReview(['rating' => 5]);
        $this->createReview(['rating' => 3]);

        $this->makeRequest()->assertJsonCount(2, 'data.reviews.data');
    }
}
