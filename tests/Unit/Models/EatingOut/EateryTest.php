<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryMapPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use App\Models\EatingOut\EateryCheck;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\SealiacOverview;
use App\Support\Helpers;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->build(Eatery::class)
            ->withoutSlug()
            ->has($this->build(EateryFeature::class), 'features')
            ->create([
                'venue_type_id' => EateryVenueType::query()->first()->id,
                'cuisine_id' => EateryCuisine::query()->first()->id,
            ]);
    }

    #[Test]
    public function itCreatesASlug(): void
    {
        $this->assertNotNull($this->eatery->slug);
        $this->assertEquals(Str::slug($this->eatery->name), $this->eatery->slug);
    }

    #[Test]
    public function itDispatchesTheCreateOpenGraphImageJobWhenSavedForEateryAndTownAndCounty(): void
    {
        config()->set('coeliac.generate_og_images', true);
        Bus::fake();

        $county = $this->build(EateryCounty::class)->createQuietly();
        $town = $this->build(EateryTown::class)->createQuietly([
            'county_id' => $county->id,
        ]);

        $eatery = $this->create(Eatery::class, [
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        $dispatchedModels = [];

        Bus::assertDispatched(CreateEatingOutOpenGraphImageJob::class, function (CreateEatingOutOpenGraphImageJob $job) use (&$dispatchedModels) {
            $dispatchedModels[] = $job->model;

            return true;
        });

        $this->assertCount(3, $dispatchedModels);
        $this->assertTrue($eatery->is($dispatchedModels[0]));
        $this->assertTrue($town->is($dispatchedModels[1]));
        $this->assertTrue($county->is($dispatchedModels[2]));
    }

    #[Test]
    public function itDispatchesTheCreateOpenGraphImageJobWhenSaved(): void
    {
        config()->set('coeliac.generate_og_images', true);

        Bus::fake();

        $this->create(Eatery::class);

        Bus::assertDispatched(CreateEateryAppPageOpenGraphImageJob::class);
        Bus::assertDispatched(CreateEateryMapPageOpenGraphImageJob::class);
        Bus::assertDispatched(CreateEateryIndexPageOpenGraphImageJob::class);
    }

    #[Test]
    public function itHasATown(): void
    {
        $this->assertEquals(1, $this->eatery->town()->count());
    }

    #[Test]
    public function itHasACounty(): void
    {
        $this->assertEquals(1, $this->eatery->county()->count());
    }

    #[Test]
    public function itHasACountry(): void
    {
        $this->assertEquals(1, $this->eatery->country()->count());
    }

    #[Test]
    public function itHasFeatures(): void
    {
        $this->assertEquals(1, $this->eatery->features()->count());
    }

    #[Test]
    public function itHasAVenueType(): void
    {
        $this->assertEquals(1, $this->eatery->venueType()->count());
    }

    #[Test]
    public function itHasACuisineType(): void
    {
        $this->assertEquals(1, $this->eatery->cuisine()->count());
    }

    #[Test]
    public function itCanHaveOpeningTimes(): void
    {
        $this->assertNull($this->eatery->openingTimes);

        $openingTimes = $this->build(EateryOpeningTimes::class)
            ->forEatery($this->eatery)
            ->create();

        $this->assertNotNull($this->eatery->refresh()->openingTimes);
        $this->assertTrue($this->eatery->openingTimes->is($openingTimes));
    }

    #[Test]
    public function itHasAnHasCategoriesScope(): void
    {
        $this->assertInstanceOf(Builder::class, Eatery::query()->hasCategories([]));
    }

    #[Test]
    public function itHasAnHasVenueTypesScope(): void
    {
        $this->assertInstanceOf(Builder::class, Eatery::query()->hasVenueTypes([]));
    }

    #[Test]
    public function itHasAnHasFeaturesScope(): void
    {
        $this->assertInstanceOf(Builder::class, Eatery::query()->hasFeatures([]));
    }

    #[Test]
    public function itHasAStaticBuilderForLatLngSearch(): void
    {
        $latLng = new LatLng(51.1, 0.23);

        $builder = Eatery::algoliaSearchAroundLatLng($latLng);

        $parameters = Arr::get((new ReflectionClosure($builder->callback))->getUseVariables(), 'parameters');

        $this->assertArrayHasKey('aroundLatLng', $parameters);
        $this->assertEquals($latLng->toString(), $parameters['aroundLatLng']);
    }

    #[Test]
    public function itIncludesTheSearchRadiusInTheLatLngSearch(): void
    {
        $latLng = new LatLng(51.1, 0.23);

        $builder = Eatery::algoliaSearchAroundLatLng($latLng, 5);

        $parameters = Arr::get((new ReflectionClosure($builder->callback))->getUseVariables(), 'parameters');

        $this->assertArrayHasKey('aroundRadius', $parameters);
        $this->assertEquals(Helpers::milesToMeters(5), $parameters['aroundRadius']);
    }

    #[Test]
    public function itClearsCacheWhenARowIsCreated(): void
    {
        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if (str_contains($key, '{')) {
                continue;
            }

            Cache::put($key, 'foo');

            $this->create(Eatery::class);

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itClearsCacheWhenARowIsUpdated(): void
    {
        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if (str_contains($key, '{')) {
                continue;
            }

            $eatery = $this->create(Eatery::class);

            Cache::put($key, 'foo');

            $eatery->update();

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itCanClearWildCardCacheEntriesWhenARecordIsCreated(): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
        ]);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if ( ! str_contains($key, '{')) {
                continue;
            }

            $key = str_replace('{county.slug}', $county->slug, $key);

            Cache::put($key, 'foo');

            $this->create(Eatery::class, [
                'county_id' => $county->id,
                'town_id' => $town->id,
            ]);

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itCanClearWildCardCacheEntriesWhenARecordIsUpdated(): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
        ]);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if ( ! str_contains($key, '{')) {
                continue;
            }

            $eatery = $this->create(Eatery::class, [
                'county_id' => $county->id,
                'town_id' => $town->id,
            ]);

            $key = str_replace('{county.slug}', $county->slug, $key);

            Cache::put($key, 'foo');

            $eatery->update();

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itCanHaveManySealiacOverviews(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->forEatery($eatery)
            ->create();

        $this->assertCount(5, $eatery->sealiacOverviews);
    }

    #[Test]
    public function itCanGetTheLatestSealiacOverview(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->sequence(fn (Sequence $sequence) => [
                'created_at' => now()->subDays($sequence->index + 1),
            ])
            ->forEatery($eatery)
            ->create();

        $latestOverview = $this->build(SealiacOverview::class)
            ->forEatery($eatery)
            ->create();

        $this->assertTrue($latestOverview->is($eatery->sealiacOverview));
    }

    #[Test]
    public function itReturnsNullForTheLatestSealiacOverviewIfItIsInvalidated(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->sequence(fn (Sequence $sequence) => [
                'created_at' => now()->subDays($sequence->index + 1),
            ])
            ->forEatery($eatery)
            ->invalidated()
            ->create();

        $this->build(SealiacOverview::class)
            ->forEatery($eatery)
            ->invalidated()
            ->create();

        $this->assertNull($eatery->sealiacOverview);
    }

    #[Test]
    public function itCanHaveACheck(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->assertNull($eatery->check);

        $check = $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $eatery->id,
        ]);

        $this->assertNotNull($eatery->refresh()->check);
        $this->assertTrue($check->is($eatery->check));
    }

    #[Test]
    public function itCanHaveAlerts(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->assertEmpty($eatery->alerts);

        $this->build(EateryAlert::class)
            ->count(3)
            ->on($eatery)
            ->create();

        $this->assertCount(3, $eatery->refresh()->alerts);
    }
}
