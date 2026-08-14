<?php

declare(strict_types=1);

namespace Tests\Unit\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Queries\EatingOut\TownReviewsQuery;
use App\Resources\EatingOut\CountyEateryResource;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TownReviewsQueryTest extends TestCase
{
    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->build(Eatery::class)
            ->count(5)
            ->create(['town_id' => $this->town->id]);

        $this->town->eateries->each(function (Eatery $eatery, $index): void {
            $this->build(EateryReview::class)
                ->count(5 - $index)
                ->create([
                    'wheretoeat_id' => $eatery->id,
                    'rating' => 5 - $index,
                    'approved' => true,
                ]);
        });
    }

    #[Test]
    public function itReturnsAResourceForEachEatery(): void
    {
        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $eateries->each(fn ($eatery) => $this->assertInstanceOf(CountyEateryResource::class, $eatery));
    }

    #[Test]
    public function itOrdersTheEateriesByTheGivenRatingClause(): void
    {
        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $this->assertGreaterThan($eateries->skip(1)->first()->resource->rating_count, $eateries->first()->resource->rating_count);
    }

    #[Test]
    public function itLimitsTheResultsToThree(): void
    {
        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $this->assertCount(3, $eateries);
    }

    #[Test]
    public function itOnlyCountsApprovedReviewsInTheRatingCount(): void
    {
        $eatery = $this->build(Eatery::class)->create(['town_id' => $this->town->id]);

        $this->build(EateryReview::class)
            ->count(3)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'approved' => false,
                'review' => null,
            ]);

        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count asc');

        $this->assertSame($eatery->id, $eateries->first()->resource->id);
        $this->assertSame(0, (int) $eateries->first()->resource->rating_count);
    }

    #[Test]
    public function itExcludesClosedDownEateries(): void
    {
        $eatery = $this->build(Eatery::class)->closedDown()->create(['town_id' => $this->town->id]);

        $this->build(EateryReview::class)
            ->count(10)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'rating' => 5,
                'approved' => true,
            ]);

        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itExcludesEateriesWithNoReviews(): void
    {
        $eatery = $this->build(Eatery::class)->create(['town_id' => $this->town->id]);

        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itOnlyIncludesEateriesFromTheGivenTown(): void
    {
        $otherTown = $this->build(EateryTown::class)->create();

        $eatery = $this->build(Eatery::class)->create(['town_id' => $otherTown->id]);

        $this->build(EateryReview::class)
            ->count(10)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'approved' => true,
            ]);

        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itSetsTheTownRelationOnTheEateries(): void
    {
        $eateries = app(TownReviewsQuery::class)($this->town, 'rating_count desc');

        $eateries->each(function (CountyEateryResource $resource): void {
            $this->assertTrue($resource->resource->relationLoaded('town'));
            $this->assertSame($this->town->id, $resource->resource->town->id);
        });
    }
}
