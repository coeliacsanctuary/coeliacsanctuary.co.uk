<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\NationwideBranch;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

class GetFiltersForSearchResultsTest extends GetFiltersTest
{
    protected Collection $branches;

    protected EaterySearchTerm $searchTerm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branches = $this->build(NationwideBranch::class)
            ->count(5)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ]);

        $this->searchTerm = $this->create(EaterySearchTerm::class);

        Cache::put("search-filters-{$this->searchTerm->key}", [
            'eateryIds' => $this->eateries->pluck('id')->toArray(),
            'branchIds' => $this->branches->pluck('id')->toArray(),
        ]);
    }

    #[Test]
    public function itCountsTheEateriesByTheCachedSearchIds(): void
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
    public function itCountsTheBranchesByTheCachedSearchIds(): void
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

    protected function getFilters(array $filters = []): array
    {
        return app(GetFiltersForSearchResults::class)->usingSearchKey($this->searchTerm->key)->handle($filters);
    }
}
