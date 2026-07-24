<?php

declare(strict_types=1);

namespace Tests\Unit\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Queries\EatingOut\CountyReviewsQuery;
use App\Resources\EatingOut\CountyEateryResource;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountyReviewsQueryTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();

        $this->build(Eatery::class)
            ->count(5)
            ->create(['county_id' => $this->county->id]);

        $this->county->eateries->each(function (Eatery $eatery, $index): void {
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
        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $eateries->each(fn ($eatery) => $this->assertInstanceOf(CountyEateryResource::class, $eatery));
    }

    #[Test]
    public function itOrdersTheEateriesByTheGivenRatingClause(): void
    {
        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $this->assertGreaterThan($eateries->skip(1)->first()->resource->rating_count, $eateries->first()->resource->rating_count);
    }

    #[Test]
    public function itLimitsTheResultsToThree(): void
    {
        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $this->assertCount(3, $eateries);
    }

    #[Test]
    public function itOnlyCountsApprovedReviewsInTheRatingCount(): void
    {
        $eatery = $this->build(Eatery::class)->create(['county_id' => $this->county->id]);

        $this->build(EateryReview::class)
            ->count(3)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'approved' => false,
                'review' => null,
            ]);

        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count asc');

        $this->assertSame($eatery->id, $eateries->first()->resource->id);
        $this->assertSame(0, (int) $eateries->first()->resource->rating_count);
    }

    #[Test]
    public function itExcludesClosedDownEateries(): void
    {
        $eatery = $this->build(Eatery::class)->closedDown()->create(['county_id' => $this->county->id]);

        $this->build(EateryReview::class)
            ->count(10)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'rating' => 5,
                'approved' => true,
            ]);

        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itExcludesEateriesWithNoReviews(): void
    {
        $eatery = $this->build(Eatery::class)->create(['county_id' => $this->county->id]);

        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itOnlyIncludesEateriesFromTheGivenCounty(): void
    {
        $otherCounty = $this->build(EateryCounty::class)->create();

        $eatery = $this->build(Eatery::class)->create(['county_id' => $otherCounty->id]);

        $this->build(EateryReview::class)
            ->count(10)
            ->create([
                'wheretoeat_id' => $eatery->id,
                'approved' => true,
            ]);

        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $eatery->id));
    }

    #[Test]
    public function itSetsTheCountyRelationOnTheEateries(): void
    {
        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $eateries->each(function (CountyEateryResource $resource): void {
            $this->assertTrue($resource->resource->relationLoaded('county'));
            $this->assertSame($this->county->id, $resource->resource->county->id);
        });
    }

    #[Test]
    public function itSetsTheCountyRelationOnTheTownOfEachEatery(): void
    {
        $eateries = app(CountyReviewsQuery::class)($this->county, 'rating_count desc');

        $eateries->each(function (CountyEateryResource $resource): void {
            $town = $resource->resource->town;

            $this->assertTrue($town->relationLoaded('county'));
            $this->assertSame($this->county->id, $town->county->id);
        });
    }
}
