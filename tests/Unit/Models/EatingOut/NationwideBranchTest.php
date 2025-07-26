<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryMapPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NationwideBranchTest extends TestCase
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
    public function itDispatchesTheCreateOpenGraphImageJobWhenSavedForBranchAndEateryAndTownAndCounty(): void
    {
        config()->set('coeliac.generate_og_images', true);
        Bus::fake();

        $county = $this->build(EateryCounty::class)->createQuietly();
        $town = $this->build(EateryTown::class)->createQuietly([
            'county_id' => $county->id,
        ]);

        $eatery = $this->build(Eatery::class)->createQuietly([
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        $dispatchedModels = [];

        Bus::assertDispatched(CreateEatingOutOpenGraphImageJob::class, function (CreateEatingOutOpenGraphImageJob $job) use (&$dispatchedModels) {
            $dispatchedModels[] = $job->model;

            return true;
        });

        $this->assertCount(4, $dispatchedModels);
        $this->assertTrue($branch->is($dispatchedModels[0]));
        $this->assertTrue($eatery->is($dispatchedModels[1]));
        $this->assertTrue($town->is($dispatchedModels[2]));
        $this->assertTrue($county->is($dispatchedModels[3]));
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
    public function itClearsCacheWhenARowIsCreated(): void
    {
        $eatery = $this->create(Eatery::class);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if (str_contains($key, '{')) {
                continue;
            }

            Cache::put($key, 'foo');

            $this->create(NationwideBranch::class, [
                'wheretoeat_id' => $eatery->id,
            ]);

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itClearsCacheWhenARowIsUpdated(): void
    {
        $eatery = $this->create(Eatery::class);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if (str_contains($key, '{')) {
                continue;
            }

            $branch = $this->create(NationwideBranch::class, [
                'wheretoeat_id' => $eatery->id,
            ]);

            Cache::put($key, 'foo');

            $branch->update();

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

        $eatery = $this->create(Eatery::class, [
            'county_id' => $county->id,
            'town_id' => $town->id,
        ]);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if ( ! str_contains($key, '{')) {
                continue;
            }

            $key = str_replace('{county.slug}', $county->slug, $key);

            Cache::put($key, 'foo');

            $this->create(NationwideBranch::class, [
                'wheretoeat_id' => $eatery->id,
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

        $eatery = $this->create(Eatery::class, [
            'county_id' => $county->id,
            'town_id' => $town->id,
        ]);

        foreach (config('coeliac.cacheable.eating-out') as $key) {
            if ( ! str_contains($key, '{')) {
                continue;
            }

            $eatery = $this->create(NationwideBranch::class, [
                'wheretoeat_id' => $eatery->id,
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
        $branch = $this->create(NationwideBranch::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->forNationwideBranch($branch)
            ->create();

        $this->assertCount(5, $branch->sealiacOverviews);
    }

    #[Test]
    public function itCanGetTheLatestSealiacOverview(): void
    {
        $branch = $this->create(NationwideBranch::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->sequence(fn (Sequence $sequence) => [
                'created_at' => now()->subDays($sequence->index + 1),
            ])
            ->forNationwideBranch($branch)
            ->create();

        $latestOverview = $this->build(SealiacOverview::class)
            ->forNationwideBranch($branch)
            ->create();

        $this->assertTrue($latestOverview->is($branch->sealiacOverview));
    }

    #[Test]
    public function itReturnsNullForTheLatestSealiacOverviewIfItIsInvalidated(): void
    {
        $branch = $this->create(NationwideBranch::class);

        $this->build(SealiacOverview::class)
            ->count(5)
            ->sequence(fn (Sequence $sequence) => [
                'created_at' => now()->subDays($sequence->index + 1),
            ])
            ->forNationwideBranch($branch)
            ->invalidated()
            ->create();

        $this->build(SealiacOverview::class)
            ->forNationwideBranch($branch)
            ->invalidated()
            ->create();

        $this->assertNull($branch->sealiacOverview);
    }
}
