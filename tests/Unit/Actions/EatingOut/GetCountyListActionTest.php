<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetCountyListAction;
use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCountyListActionTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        EateryCountryDescriptionAgent::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        foreach (range(2, 6) as $index) {
            $country = $this->build(EateryCountry::class)
                ->state([
                    'id' => $index,
                    'country' => $this->faker->unique->country,
                ])
                ->create();

            $counties = $this->build(EateryCounty::class)
                ->state(['country_id' => $country->id])
                ->count(5)
                ->create();

            $counties->each(function (EateryCounty $county) use ($country): void {
                $towns = $this->build(EateryTown::class)
                    ->state(['county_id' => $county->id])
                    ->count(5)
                    ->create();

                $towns->each(function (EateryTown $town) use ($county, $country): void {
                    $this->build(Eatery::class)
                        ->state(['country_id' => $country->id, 'county_id' => $county->id, 'town_id' => $town->id])
                        ->count(5)
                        ->create();
                });
            });
        }
    }

    #[Test]
    public function itReturnsACollection(): void
    {
        $this->assertInstanceOf(
            Collection::class,
            app(GetCountyListAction::class)->handle(),
        );
    }

    #[Test]
    public function eachCountryHasADescriptionKey(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $this->assertArrayHasKey('description', $item);
        });
    }

    #[Test]
    public function itExcludesTheNationwideCountry(): void
    {
        $this->build(EateryCountry::class)
            ->state(['country' => 'Nationwide'])
            ->create();

        $collection = app(GetCountyListAction::class)->handle();

        $this->assertNull($collection->firstWhere('name', 'Nationwide'));
    }

    #[Test]
    public function eachCountryHasAListPropertyThatIsAnArray(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $this->assertArrayHasKey('list', $item);
        });
    }

    #[Test]
    public function eachCountryListHasANameSlugAndTotal(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            foreach ($item['list'] as $county) {
                $this->assertArrayHasKeys(['name', 'slug', 'total'], (array) $county);
            }
        });
    }

    #[Test]
    public function eachCountryListItemHasAllExpectedKeys(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            foreach ($item['list'] as $county) {
                $this->assertArrayHasKeys(
                    ['name', 'slug', 'image', 'eateries', 'attractions', 'hotels', 'branches', 'total', 'review_count', 'avg_rating'],
                    (array) $county,
                );
            }
        });
    }

    #[Test]
    public function eachCountryListHasTheCountiesInThatCounty(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $listedCounties = collect($item['list'])->map(fn ($county) => (array) $county)->pluck('name');

            EateryCountry::query()
                ->firstWhere('country', $item['name'])
                ->counties()
                ->whereHas('eateries', fn (Builder $builder) => $builder->where('live', true))
                ->pluck('county')
                ->each(fn ($county) => $this->assertContains($county, $listedCounties));
        });
    }

    #[Test]
    public function itListsTheNumberOfCountiesInEachCountry(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $this->assertArrayHasKey('counties', $item);

            $counties = collect($item['list'])->count();

            $this->assertEquals($counties, $item['counties']);
        });
    }

    #[Test]
    public function itListsTheNumberOfEateriesAndBranchesInEachCounty(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $this->assertArrayHasKey('eateries', $item);

            $eateries = EateryCountry::query()
                ->firstWhere('country', $item['name'])
                ->eateries()
                ->withCount(['nationwideBranches'])
                ->get();

            $total = $eateries->count() + $eateries->pluck('nationwide_branches_count')->sum();

            $this->assertEquals($total, $item['eateries']);
        });
    }

    #[Test]
    public function itCachesTheResults(): void
    {
        $key = config('coeliac.cacheable.eating-out.index-counts');

        $this->assertFalse(Cache::has($key));

        app(GetCountyListAction::class)->handle();

        $this->assertTrue(Cache::has($key));
    }

    #[Test]
    public function itGetsTheResultsFromTheCache(): void
    {
        Cache::partialMock()
            ->shouldReceive('rememberForever')
            ->once()
            ->andReturn(collect());

        app(GetCountyListAction::class)->handle();
    }

    #[Test]
    public function eachCountryHasATopCountiesKey(): void
    {
        $collection = app(GetCountyListAction::class)->handle();

        $collection->each(function (array $item): void {
            $this->assertArrayHasKey('top_counties', $item);
        });
    }

    #[Test]
    public function topCountiesIsLimitedToThree(): void
    {
        $country = EateryCountry::find(2);

        $country->counties->each(function (EateryCounty $county): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($county->eateries()->first())
                ->count(3)
                ->create();
        });

        Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));

        $collection = app(GetCountyListAction::class)->handle();
        $countryResult = $collection->firstWhere('name', $country->country);

        $this->assertCount(3, $countryResult['top_counties']);
    }

    #[Test]
    public function topCountiesWithNoReviewsAreExcluded(): void
    {
        $country = EateryCountry::find(2);

        $country->counties->take(2)->each(function (EateryCounty $county): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($county->eateries()->first())
                ->create();
        });

        Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));

        $collection = app(GetCountyListAction::class)->handle();
        $countryResult = $collection->firstWhere('name', $country->country);

        $this->assertCount(2, $countryResult['top_counties']);
    }

    #[Test]
    public function topCountiesAreRankedByBayesianScore(): void
    {
        $country = EateryCountry::find(2);
        $counties = $country->counties->take(3)->values();

        // 1 review @ 5 stars → Bayesian ≈ 4.17
        $this->build(EateryReview::class)
            ->approved()
            ->on($counties[0]->eateries()->first())
            ->state(['rating' => 5])
            ->create();

        // 10 reviews @ 5 stars → Bayesian ≈ 4.67 (should rank first)
        $this->build(EateryReview::class)
            ->approved()
            ->on($counties[1]->eateries()->first())
            ->state(['rating' => 5])
            ->count(10)
            ->create();

        // 10 reviews @ 4 stars → Bayesian = 4.0 (should rank last)
        $this->build(EateryReview::class)
            ->approved()
            ->on($counties[2]->eateries()->first())
            ->state(['rating' => 4])
            ->count(10)
            ->create();

        Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));

        $collection = app(GetCountyListAction::class)->handle();
        $countryResult = $collection->firstWhere('name', $country->country);
        $topCounties = collect($countryResult['top_counties']);

        $this->assertEquals($counties[1]->county, $topCounties[0]['name']); // 4.67
        $this->assertEquals($counties[0]->county, $topCounties[1]['name']); // 4.17
        $this->assertEquals($counties[2]->county, $topCounties[2]['name']); // 4.0
    }

    #[Test]
    public function topCountiesUseTheSameFormatAsList(): void
    {
        $country = EateryCountry::find(2);

        $this->build(EateryReview::class)
            ->approved()
            ->on($country->counties->first()->eateries()->first())
            ->create();

        Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));

        $collection = app(GetCountyListAction::class)->handle();
        $countryResult = $collection->firstWhere('name', $country->country);

        $this->assertEquals(
            array_keys($countryResult['list']->first()),
            array_keys($countryResult['top_counties']->first()),
        );
    }
}
