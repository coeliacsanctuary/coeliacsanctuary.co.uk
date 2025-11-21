<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Services\EatingOut\Filters\GetFiltersForTown;
use PHPUnit\Framework\Attributes\Test;

class GetFiltersForTownTest extends GetFiltersTest
{
    #[Test]
    public function itCountsTheEateriesByTheTownId(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        $queries = app('db')->getQueryLog();

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `town_id` = {$this->town->id} and `live` = 1 and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1)",
            $queries[0]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `town_id` = {$this->town->id} and `live` = 1 and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1)",
            $queries[1]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `town_id` = {$this->town->id} and `live` = 1 and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1)",
            $queries[2]['query']
        );
    }

    #[Test]
    public function itCountsTheBranchesByTheTownId(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        $queries = app('db')->getQueryLog();

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `town_id` = {$this->town->id} and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $queries[0]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat_nationwide_branches` where `town_id` = {$this->town->id} and `live` = 1 and exists (select * from `wheretoeat` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
            $queries[1]['query']
        );

        $this->assertStringContainsString(
            " (select count(*) from `wheretoeat_nationwide_branches` where `town_id` = {$this->town->id} and `live` = 1 and exists (select * from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `wheretoeat_nationwide_branches`.`wheretoeat_id` = `wheretoeat`.`id` and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1) and `wheretoeat_nationwide_branches`.`live` = 1)",
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
        return app(GetFiltersForTown::class)->setTown($this->town)->handle($filters);
    }
}
