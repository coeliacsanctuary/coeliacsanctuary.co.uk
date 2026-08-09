<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetMostRatedPlacesInTownAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetMostRatedPlacesInTownActionTest extends TestCase
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
    public function itOrdersTheEateriesByTheNumberRating(): void
    {
        $eateries = $this->callAction(GetMostRatedPlacesInTownAction::class, $this->town);

        $this->assertGreaterThan($eateries->skip(1)->first()->rating_count, $eateries->first()->rating_count);
    }

    #[Test]
    public function itCachesTheMostRatedPlaces(): void
    {
        $key = str_replace(
            ['{county.slug}', '{town.slug}'],
            [$this->town->county->slug, $this->town->slug],
            config('coeliac.cacheable.eating-out.most-rated-in-town'),
        );

        $this->assertFalse(Cache::has($key));

        $this->callAction(GetMostRatedPlacesInTownAction::class, $this->town);

        $this->assertTrue(Cache::has($key));
    }

    #[Test]
    public function itGetsTheMostRatedPlacesOutOfTheCacheIfTheyExist(): void
    {
        $this->callAction(GetMostRatedPlacesInTownAction::class, $this->town);

        app('db')->enableQueryLog();

        $this->callAction(GetMostRatedPlacesInTownAction::class, $this->town);

        $this->assertEmpty(app('db')->getQueryLog());
    }
}
