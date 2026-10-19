<?php

declare(strict_types=1);

namespace Tests\Unit\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Queries\EatingOut\MostReviewsInUkQuery;
use App\Resources\EatingOut\CountyEateryResource;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MostReviewsInUkQueryTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        EateryCounty::query()->withoutGlobalScopes()->whereKey(1)->update(['county' => 'Nationwide', 'slug' => 'nationwide']);
        EateryTown::query()->withoutGlobalScopes()->whereKey(1)->update(['town' => 'Nationwide', 'slug' => 'nationwide']);

        $this->county = $this->build(EateryCounty::class)->create();
        $this->town = $this->build(EateryTown::class)->create(['county_id' => $this->county->id]);
    }

    #[Test]
    public function itExcludesNationwideEateries(): void
    {
        $chain = $this->build(Eatery::class)->create(['county_id' => 1, 'town_id' => 1]);

        $this->build(EateryReview::class)
            ->count(10)
            ->create(['wheretoeat_id' => $chain->id, 'rating' => 5, 'approved' => true]);

        $eatery = $this->build(Eatery::class)->create(['county_id' => $this->county->id, 'town_id' => $this->town->id]);

        $this->build(EateryReview::class)
            ->count(2)
            ->create(['wheretoeat_id' => $eatery->id, 'rating' => 4, 'approved' => true]);

        $eateries = app(MostReviewsInUkQuery::class)('rating_count desc, rating desc');

        $this->assertCount(1, $eateries);
        $this->assertTrue($eateries->every(fn (CountyEateryResource $resource) => $resource->resource->id !== $chain->id));
    }
}
