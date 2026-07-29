<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Jobs\EatingOut\GenerateAreaDescriptionJob;
use App\Jobs\EatingOut\GenerateBoroughDescriptionJob;
use App\Jobs\EatingOut\GenerateCountryDescriptionJob;
use App\Jobs\EatingOut\GenerateCountyDescriptionJob;
use App\Jobs\EatingOut\GenerateTownDescriptionJob;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
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
        Bus::assertDispatched(CreateEateryIndexPageOpenGraphImageJob::class);
    }

    #[Test]
    public function itDispatchesTheCountryDescriptionJobOnSave(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        $country = $this->create(EateryCountry::class, [
            'description' => 'foo bar',
        ]);

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'country_id' => $country->id,
        ]);

        Bus::assertDispatched(GenerateCountryDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotClearTheExistingCountryDescriptionOnSave(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        Bus::fake();

        $country = $this->create(EateryCountry::class, [
            'description' => 'foo bar',
        ]);

        $this->create(NationwideBranch::class, [
            'country_id' => $country->id,
        ]);

        $this->assertEquals('foo bar', $country->refresh()->description);
    }

    #[Test]
    public function itDoesNotDispatchTheCountryDescriptionJobWhenTheConfigIsDisabled(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', false);

        $country = $this->create(EateryCountry::class);

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'country_id' => $country->id,
        ]);

        Bus::assertNotDispatched(GenerateCountryDescriptionJob::class);
    }

    #[Test]
    public function itDispatchesTheCountyDescriptionJobOnSave(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        $county = EateryCounty::query()->firstOrFail();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
        ]);

        Bus::assertDispatched(GenerateCountyDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheCountyDescriptionJobWhenTheConfigIsDisabled(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', false);

        $county = EateryCounty::query()->firstOrFail();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
        ]);

        Bus::assertNotDispatched(GenerateCountyDescriptionJob::class);
    }

    /** @return array{EateryCounty, EateryTown} */
    protected function createLondonBorough(): array
    {
        $county = $this->create(EateryCounty::class, ['county' => 'London']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id, 'town' => 'Camden']);
        $area = $this->create(EateryArea::class, ['town_id' => $town->id, 'area' => 'Camden Lock']);

        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id, 'area_id' => $area->id]);

        return [$county, $town, $area];
    }

    #[Test]
    public function itDispatchesTheBoroughDescriptionAndAreaDescriptionJobsOnSaveForALondonBranch(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        [$county, $town, $area] = $this->createLondonBorough();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
            'town_id' => $town->id,
            'area_id' => $area->id
        ]);

        Bus::assertDispatched(GenerateBoroughDescriptionJob::class);
        Bus::assertDispatched(GenerateAreaDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheBoroughDescriptionOrAreaDescriptionJobsForANonLondonBranch(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        $county = EateryCounty::query()->firstOrFail();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
        ]);

        Bus::assertNotDispatched(GenerateBoroughDescriptionJob::class);
        Bus::assertNotDispatched(GenerateAreaDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheBoroughDescriptionOrAreaDescriptionJobsWhenTheConfigIsDisabled(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', false);

        [$county, $town, $area] = $this->createLondonBorough();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
            'town_id' => $town->id,
            'area_id' => $area->id,
        ]);

        Bus::assertNotDispatched(GenerateBoroughDescriptionJob::class);
        Bus::assertNotDispatched(GenerateAreaDescriptionJob::class);
    }

    #[Test]
    public function itDispatchesTheTownDescriptionJobOnSaveForANonLondonBranch(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        $county = EateryCounty::query()->firstOrFail();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
        ]);

        Bus::assertDispatched(GenerateTownDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheTownDescriptionJobForALondonBranch(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', true);

        [$county, $town, $area] = $this->createLondonBorough();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
            'town_id' => $town->id,
            'area_id' => $area->id,
        ]);

        Bus::assertNotDispatched(GenerateTownDescriptionJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheTownDescriptionJobWhenTheConfigIsDisabled(): void
    {
        config()->set('coeliac.generate_eatery_ai_descriptions', false);

        $county = EateryCounty::query()->firstOrFail();

        Bus::fake();

        $this->create(NationwideBranch::class, [
            'county_id' => $county->id,
        ]);

        Bus::assertNotDispatched(GenerateTownDescriptionJob::class);
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
            $key = str_replace('{town.slug}', $town->slug, $key);

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
            $key = str_replace('{town.slug}', $town->slug, $key);

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

    #[Test]
    public function itCanBeAssociatedWithACollectionGroup(): void
    {
        $branch = $this->create(NationwideBranch::class);
        $group = $this->create(CollectionGroup::class, ['collection_id' => $this->create(Collection::class)->id]);

        $this->assertEmpty($branch->associatedCollectionGroups);

        $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $group->id,
            'item_id' => $branch->id,
            'item_type' => NationwideBranch::class,
        ]);

        $this->assertCount(1, $branch->refresh()->associatedCollectionGroups);
    }
}
