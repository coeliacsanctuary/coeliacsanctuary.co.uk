<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Collections;

use App\Actions\Collections\GenerateCollectionFromPendingEateries;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateCollectionFromPendingEateriesTest extends TestCase
{
    protected EateryCountry $country;

    protected EateryCounty $county;

    protected EateryTown $town;

    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->country = EateryCountry::query()->withoutGlobalScopes()->first();
        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->eatery = $this->create(Eatery::class, [
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);
    }

    protected function makePendingEatery(Eatery $eatery, ?int $branchId = null): PendingEatery
    {
        return new PendingEatery(
            id: $eatery->id,
            branchId: $branchId,
            ordering: $eatery->name,
        );
    }

    #[Test]
    public function itCreatesACollectionWithTheCorrectFields(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'My Test Collection', 'town');

        $collection = Collection::query()->withoutGlobalScopes()->first();

        $this->assertEquals('My Test Collection', $collection->title);
        $this->assertFalse($collection->live);
        $this->assertTrue($collection->draft);
        $this->assertNull($collection->publish_at);
        $this->assertEquals('', $collection->meta_keywords);
        $this->assertEquals('', $collection->meta_description);
        $this->assertEquals('', $collection->long_description);
        $this->assertEquals('', $collection->body);
    }

    #[Test]
    public function itSlugifiesTheName(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'My Test Collection', 'town');

        $collection = Collection::query()->withoutGlobalScopes()->first();

        $this->assertEquals('my-test-collection', $collection->slug);
    }

    #[Test]
    public function itReturnsTheCreatedCollectionInstance(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $result = $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('Test', $result->title);
    }

    #[Test]
    public function itCreatesAGroupForEachUniqueOrderingField(): void
    {
        $secondTown = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Manchester']);
        $secondEatery = $this->create(Eatery::class, ['town_id' => $secondTown->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $this->assertDatabaseCount(CollectionGroup::class, 2);
    }

    #[Test]
    public function itCreatesGroupsWithTheCorrectTownTitle(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $group = CollectionGroup::query()->first();

        $this->assertEquals(
            "{$this->town->town}, {$this->county->county}, {$this->country->country}",
            $group->title,
        );
    }

    #[Test]
    public function itCreatesGroupsWithTheCorrectCountyTitle(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'county');

        $group = CollectionGroup::query()->first();

        $this->assertEquals(
            "{$this->county->county}, {$this->country->country}",
            $group->title,
        );
    }

    #[Test]
    public function itCreatesGroupsWithTheCorrectCountryTitle(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'country');

        $group = CollectionGroup::query()->first();

        $this->assertEquals($this->country->country, $group->title);
    }

    #[Test]
    public function itCreatesGroupsWithTheCorrectAreaTitle(): void
    {
        $area = $this->create(EateryArea::class, [
            'area' => 'Kensington',
            'town_id' => $this->town->id,
        ]);

        $eateryInArea = $this->create(Eatery::class, [
            'town_id' => $this->town->id,
            'area_id' => $area->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);

        $pending = collect([new PendingEatery(id: $eateryInArea->id, branchId: null, ordering: $eateryInArea->name)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'area');

        $group = CollectionGroup::query()->first();

        $this->assertEquals(
            "{$area->area}, {$this->town->town}, {$this->county->county}, {$this->country->country}",
            $group->title,
        );
    }

    #[Test]
    public function itCreatesGroupsInOrderOfFirstAppearanceInPendingEateries(): void
    {
        $secondTown = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Aardvark Town']);
        $secondEatery = $this->create(Eatery::class, ['town_id' => $secondTown->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($secondEatery),
            $this->makePendingEatery($this->eatery),
        ]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $groups = CollectionGroup::query()->orderBy('position')->get();

        $this->assertEquals(
            "{$secondTown->town}, {$this->county->county}, {$this->country->country}",
            $groups->first()->title,
        );

        $this->assertEquals(
            "{$this->town->town}, {$this->county->county}, {$this->country->country}",
            $groups->last()->title,
        );
    }

    #[Test]
    public function itCreatesCollectionGroupItemsForEachPendingEatery(): void
    {
        $secondEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $this->assertDatabaseCount(CollectionGroupItem::class, 2);
    }

    #[Test]
    public function itSetsItemIdToEateryIdForNonBranchPendingEateries(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $item = CollectionGroupItem::query()->first();

        $this->assertEquals($this->eatery->id, $item->item_id);
    }

    #[Test]
    public function itSetsItemTypeToEateryForNonBranchPendingEateries(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $item = CollectionGroupItem::query()->first();

        $this->assertEquals(Eatery::class, $item->item_type);
    }

    #[Test]
    public function itSetsItemIdToBranchIdForBranchPendingEateries(): void
    {
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);

        $pending = collect([$this->makePendingEatery($this->eatery, $branch->id)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $item = CollectionGroupItem::query()->first();

        $this->assertEquals($branch->id, $item->item_id);
    }

    #[Test]
    public function itSetsItemTypeToNationwideBranchForBranchPendingEateries(): void
    {
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);

        $pending = collect([$this->makePendingEatery($this->eatery, $branch->id)]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $item = CollectionGroupItem::query()->first();

        $this->assertEquals(NationwideBranch::class, $item->item_type);
    }

    #[Test]
    public function itPreservesTheOrderOfPendingEateriesWithinEachGroup(): void
    {
        $secondEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);
        $thirdEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($thirdEatery),
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $items = CollectionGroupItem::query()->orderBy('position')->get();

        $this->assertEquals($thirdEatery->id, $items->get(0)->item_id);
        $this->assertEquals($this->eatery->id, $items->get(1)->item_id);
        $this->assertEquals($secondEatery->id, $items->get(2)->item_id);
    }

    #[Test]
    public function itQueriesEateriesInASingleBulkQuery(): void
    {
        $secondEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        DB::enableQueryLog();

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $eateryQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_starts_with($query['query'], 'select `id`') && str_contains($query['query'], 'from `wheretoeat`'))
            ->values();

        $this->assertCount(1, $eateryQueries);
    }

    #[Test]
    public function itQueriesGroupingModelInASingleBulkQuery(): void
    {
        $secondEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        DB::enableQueryLog();

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $townQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_starts_with($query['query'], 'select * from `wheretoeat_towns`'))
            ->values();

        $this->assertCount(1, $townQueries);
    }

    #[Test]
    public function itQueriesBranchesInASingleBulkQuery(): void
    {
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);

        $secondEatery = $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $secondBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $secondEatery->id,
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
            'country_id' => $this->country->id,
        ]);

        $pending = collect([
            $this->makePendingEatery($this->eatery, $branch->id),
            $this->makePendingEatery($secondEatery, $secondBranch->id),
        ]);

        DB::enableQueryLog();

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $branchQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_starts_with($query['query'], 'select `id`') && str_contains($query['query'], 'from `wheretoeat_nationwide_branches`'))
            ->values();

        $this->assertCount(1, $branchQueries);
    }

    #[Test]
    public function itCreatesGroupsAssociatedWithTheCollection(): void
    {
        $pending = collect([$this->makePendingEatery($this->eatery)]);

        $result = $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $group = CollectionGroup::query()->first();

        $this->assertEquals($result->id, $group->collection_id);
    }

    #[Test]
    public function itCreatesItemsAssociatedWithTheCorrectGroup(): void
    {
        $secondTown = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Manchester']);
        $secondEatery = $this->create(Eatery::class, ['town_id' => $secondTown->id, 'county_id' => $this->county->id, 'country_id' => $this->country->id]);

        $pending = collect([
            $this->makePendingEatery($this->eatery),
            $this->makePendingEatery($secondEatery),
        ]);

        $this->callAction(GenerateCollectionFromPendingEateries::class, $pending, 'Test', 'town');

        $firstGroup = CollectionGroup::query()->orderBy('position')->first();
        $secondGroup = CollectionGroup::query()->orderBy('position')->skip(1)->first();

        $this->assertEquals(1, CollectionGroupItem::query()->where('collection_group_id', $firstGroup->id)->count());
        $this->assertEquals(1, CollectionGroupItem::query()->where('collection_group_id', $secondGroup->id)->count());
    }
}
