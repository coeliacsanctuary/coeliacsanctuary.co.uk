<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;
use App\Support\State\EatingOut\Search\SearchResultIdsState;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class GetFiltersForSearchResultsTest extends GetFiltersTest
{
    protected Collection $branches;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branches = $this->build(NationwideBranch::class)
            ->count(5)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ]);

        SearchResultIdsState::$eateryIds = $this->eateries->pluck('id')->toArray();
        SearchResultIdsState::$branchIds = $this->branches->pluck('id')->toArray();
    }

    #[Test]
    public function itCountsTheEateriesByTheSearchResultIds(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        $queries = app('db')->getQueryLog();

        $ids = $this->eateries->pluck('id')->join(', ');

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1)",
            $queries[0]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1)",
            $queries[1]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `id` in ({$ids}) and `live` = 1 and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1)",
            $queries[2]['query']
        );
    }

    #[Test]
    public function itCountsTheBranchesByTheSearchResultIds(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        $queries = app('db')->getQueryLog();

        $ids = $this->branches->pluck('id')->join(', ');

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $queries[0]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $queries[1]['query']
        );

        $this->assertStringContainsString(
            " (select count(*) from `wheretoeat_nationwide_branches` where `id` in ({$ids}) and `live` = 1 and exists (select * from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $queries[2]['query']
        );
    }

    #[Test]
    public function itAddsTheEateryAndBranchCountsTogether(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        foreach (app('db')->getQueryLog() as $query) {
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

        foreach (app('db')->getQueryLog() as $query) {
            $this->assertStringContainsString(
                ') as eateries_count from',
                $query['query']
            );
        }
    }

    #[Test]
    public function itCountsAgainstTheSearchResultsRegardlessOfTheAppliedFilters(): void
    {
        $unfiltered = $this->getFilters();

        $venueType = EateryVenueType::query()->orderBy('venue_type')->firstOrFail();

        $filtered = $this->getFilters(['venueTypes' => [$venueType->slug]]);

        $this->assertEquals(
            collect($unfiltered['categories'])->pluck('label')->all(),
            collect($filtered['categories'])->pluck('label')->all(),
        );
    }

    #[Test]
    public function itKeepsTheOtherOptionsAvailableWhenAFilterIsApplied(): void
    {
        [$first, $second] = EateryVenueType::query()->orderBy('venue_type')->take(2)->get()->all();

        $this->eateries->last()->update(['venue_type_id' => $second->id]);

        $venueTypes = collect($this->getFilters(['venueTypes' => [$first->slug]])['venueTypes']);

        $this->assertTrue($venueTypes->firstWhere('value', $first->slug)['checked']);

        $this->assertNotNull(
            $venueTypes->firstWhere('value', $second->slug),
            'Filtering by one venue type removed the other venue types from the list.',
        );
    }

    protected function getFilters(array $filters = []): array
    {
        return app(GetFiltersForSearchResults::class)->handle($filters);
    }
}
