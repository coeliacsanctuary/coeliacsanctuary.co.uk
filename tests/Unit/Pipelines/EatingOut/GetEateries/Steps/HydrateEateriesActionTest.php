<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use Illuminate\Support\Collection;

class HydrateEateriesActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 50;

    protected int $reviewsToCreate = 50;

    protected int $branchesToCreate = 50;

    #[Test]
    public function itReturnsTheHydratedEateries(): void
    {
        $hydratedEateries = $this->callHydrateEateriesAction();

        $this->assertInstanceOf(Collection::class, $hydratedEateries->hydrated);
        $this->assertInstanceOf(Eatery::class, $hydratedEateries->hydrated->first());
    }

    #[Test]
    public function itOnlyLoadsLimitedReviewColumnsWhenHydrateFullReviewsIsFalse(): void
    {
        $hydratedEateries = $this->callHydrateEateriesAction(hydrateFullReviews: false);

        $review = $hydratedEateries->hydrated->first()->reviews->first();

        $this->assertArrayHasKey('id', $review->getAttributes());
        $this->assertArrayNotHasKey('review', $review->getAttributes());
        $this->assertArrayNotHasKey('name', $review->getAttributes());
    }

    #[Test]
    public function itLoadsAllReviewColumnsWhenHydrateFullReviewsIsTrue(): void
    {
        $hydratedEateries = $this->callHydrateEateriesAction(hydrateFullReviews: true);

        $review = $hydratedEateries->hydrated->first()->reviews->first();

        $this->assertArrayHasKey('review', $review->getAttributes());
        $this->assertArrayHasKey('name', $review->getAttributes());
    }
}
