<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;

class HydrateBranchesActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 1;

    #[Test]
    public function itReturnsTheHydratedBranches(): void
    {
        $hydratedBranches = $this->callHydrateBranchesAction();

        $this->assertInstanceOf(Collection::class, $hydratedBranches->hydratedBranches);
        $this->assertInstanceOf(NationwideBranch::class, $hydratedBranches->hydratedBranches->first());
    }

    #[Test]
    public function itOnlyLoadsLimitedReviewColumnsWhenHydrateFullReviewsIsFalse(): void
    {
        $branch = NationwideBranch::query()->first();

        $this->build(EateryReview::class)->branch($branch)->approved()->create();

        $hydratedBranches = $this->callHydrateBranchesAction(hydrateFullReviews: false);

        $review = $hydratedBranches->hydratedBranches->first()->reviews->first();

        $this->assertArrayHasKey('id', $review->getAttributes());
        $this->assertArrayNotHasKey('review', $review->getAttributes());
        $this->assertArrayNotHasKey('name', $review->getAttributes());
    }

    #[Test]
    public function itLoadsAllReviewColumnsWhenHydrateFullReviewsIsTrue(): void
    {
        $branch = NationwideBranch::query()->first();

        $this->build(EateryReview::class)->branch($branch)->approved()->create();

        $hydratedBranches = $this->callHydrateBranchesAction(hydrateFullReviews: true);

        $review = $hydratedBranches->hydratedBranches->first()->reviews->first();

        $this->assertArrayHasKey('review', $review->getAttributes());
        $this->assertArrayHasKey('name', $review->getAttributes());
    }
}
