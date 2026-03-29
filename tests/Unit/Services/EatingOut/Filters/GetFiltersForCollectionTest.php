<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Models\EatingOut\EateryCollection;
use App\Models\EatingOut\NationwideBranch;
use App\Services\EatingOut\Filters\GetFiltersForCollection;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class GetFiltersForCollectionTest extends GetFiltersTest
{
    protected EateryCollection $collection;

    /** @var Collection<int, NationwideBranch> */
    protected Collection $branches;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = $this->create(EateryCollection::class);

        /** @var Collection<int, NationwideBranch> $branches */
        $branches = $this->build(NationwideBranch::class)
            ->count(5)
            ->create([
                'wheretoeat_id' => $this->eateries->first()->id,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ]);

        $this->branches = $branches;
    }

    protected function getFilters(array $filters = []): array
    {
        return app(GetFiltersForCollection::class)->setCollection($this->collection)->handle($filters);
    }

    #[Test]
    public function itReturnsTheTownsAndCountiesFilterKeys(): void
    {
        $filters = $this->getFilters();

        $this->assertArrayHasKey('towns', $filters);
        $this->assertArrayHasKey('counties', $filters);
    }

    #[Test]
    public function itReturnsTheTownFilters(): void
    {
        $townFilters = $this->getFilters()['towns'];

        $keys = ['value', 'label', 'disabled', 'checked'];

        foreach ($townFilters as $town) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $town);
            }
        }
    }

    #[Test]
    public function eachTownFilterIsNotCheckedByDefault(): void
    {
        foreach ($this->getFilters()['towns'] as $town) {
            $this->assertFalse($town['checked']);
        }
    }

    #[Test]
    public function aTownFilterCanBeCheckedViaTheRequest(): void
    {
        $townFilters = $this->getFilters(['towns' => $this->town->id])['towns'];

        $this->assertTrue($townFilters[0]['checked']);
    }

    #[Test]
    public function itReturnsTheCountyFilters(): void
    {
        $countyFilters = $this->getFilters()['counties'];

        $keys = ['value', 'label', 'disabled', 'checked'];

        foreach ($countyFilters as $county) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $county);
            }
        }
    }

    #[Test]
    public function eachCountyFilterIsNotCheckedByDefault(): void
    {
        foreach ($this->getFilters()['counties'] as $county) {
            $this->assertFalse($county['checked']);
        }
    }

    #[Test]
    public function aCountyFilterCanBeCheckedViaTheRequest(): void
    {
        $countyFilters = $this->getFilters(['counties' => $this->county->id])['counties'];

        $this->assertTrue($countyFilters[0]['checked']);
    }

    #[Test]
    public function itCountsTheEateriesByTheCollectionIds(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        // queries[0] and queries[1] are the getIds() DB::select calls for eatery/branch IDs
        $queries = collect(app('db')->getQueryLog())->slice(2)->values();

        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query0 */
        $query0 = $queries->get(0);
        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query1 */
        $query1 = $queries->get(1);
        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query2 */
        $query2 = $queries->get(2);

        $ids = $this->eateries->pluck('id')->join(', ');

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1)",
            $query0['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1)",
            $query1['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1)",
            $query2['query']
        );
    }

    #[Test]
    public function itCountsTheBranchesByTheCollectionIds(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        // queries[0] and queries[1] are the getIds() DB::select calls for eatery/branch IDs
        $queries = collect(app('db')->getQueryLog())->slice(2)->values();

        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query0 */
        $query0 = $queries->get(0);
        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query1 */
        $query1 = $queries->get(1);
        /** @var array{query: string, bindings: array<string, mixed>, time: float|null} $query2 */
        $query2 = $queries->get(2);

        $ids = $this->branches->pluck('id')->join(', ');

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $query0['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $query1['query']
        );

        $this->assertStringContainsString(
            " (select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $query2['query']
        );
    }

    #[Test]
    public function itAddsTheEateryAndBranchCountsTogether(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        // Skip getIds() queries and eager-load relation queries; only check main filter queries
        $filterQueries = collect(app('db')->getQueryLog())
            ->filter(fn (array $query) => str_contains($query['query'], 'eateries_count'));

        $this->assertNotEmpty($filterQueries);

        foreach ($filterQueries as $query) {
            $this->assertStringContainsString(
                '`live` = 1) + (select count(*) from',
                $query['query']
            );
        }
    }

    #[Test]
    public function itAliasesTheEateryAndBranchCountAsEateriesCount(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        // Skip getIds() queries and eager-load relation queries; only check main filter queries
        $filterQueries = collect(app('db')->getQueryLog())
            ->filter(fn (array $query) => str_contains($query['query'], 'eateries_count'));

        $this->assertNotEmpty($filterQueries);

        foreach ($filterQueries as $query) {
            $this->assertStringContainsString(
                ') as eateries_count from',
                $query['query']
            );
        }
    }

    #[Test]
    public function itThrowsAnExceptionWhenTheCollectionIsNotSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Collection not set');

        app(GetFiltersForCollection::class)->handle();
    }
}
