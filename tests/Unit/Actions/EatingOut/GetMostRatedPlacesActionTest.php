<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetMostRatedPlacesAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetMostRatedPlacesActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $county = $this->build(EateryCounty::class)->create();
        $town = $this->build(EateryTown::class)->create(['county_id' => $county->id]);

        $eateries = $this->build(Eatery::class)->count(5)->create([
            'county_id' => $county->id,
            'town_id' => $town->id,
        ]);

        $eateries->each(function (Eatery $eatery, $index): void {
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
    public function itOrdersTheEateriesByTheNumberRating(): void
    {
        $eateries = $this->callAction(GetMostRatedPlacesAction::class);

        $this->assertGreaterThan($eateries->skip(1)->first()->rating_count, $eateries->first()->rating_count);
    }

    #[Test]
    public function itCachesTheMostRatedPlaces(): void
    {
        $this->assertFalse(Cache::has(config('coeliac.cacheable.eating-out-reviews.most-rated')));

        $this->callAction(GetMostRatedPlacesAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.eating-out-reviews.most-rated')));
    }

    #[Test]
    public function itGetsTheMostRatedPlacesOutOfTheCacheIfTheyExist(): void
    {
        $this->callAction(GetMostRatedPlacesAction::class);

        app('db')->enableQueryLog();

        $this->callAction(GetMostRatedPlacesAction::class);

        $this->assertEmpty(app('db')->getQueryLog());
    }
}
