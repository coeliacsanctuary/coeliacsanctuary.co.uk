<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\Filters\GetFiltersForNationwide;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class GetFiltersForNationwideTest extends GetFiltersTest
{
    #[Test]
    public function itCountsTheEateriesByTheCountyId(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        $queries = app('db')->getQueryLog();

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `county_id` = {$this->county->id} and `live` = 1 and `wheretoeat_types`.`id` = `wheretoeat`.`type_id` and `live` = 1)",
            $queries[0]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` where `county_id` = {$this->county->id} and `live` = 1 and `wheretoeat_venue_types`.`id` = `wheretoeat`.`venue_type_id` and `live` = 1)",
            $queries[1]['query']
        );

        $this->assertStringContainsString(
            "(select count(*) from `wheretoeat` left join `wheretoeat_assigned_features` on `wheretoeat`.`id` = `wheretoeat_assigned_features`.`wheretoeat_id` where `county_id` = {$this->county->id} and `live` = 1 and `wheretoeat_features`.`id` = `wheretoeat_assigned_features`.`feature_id` and `live` = 1)",
            $queries[2]['query']
        );
    }

    #[Test]
    public function itDoesntCountTheNationwideBranches(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        foreach (app('db')->getQueryLog() as $query) {
            $this->assertStringNotContainsString('wheretoeat_nationwide_branches', $query['query']);
        }
    }

    #[Test]
    public function itAliasesTheEateryCountAsEateriesCount(): void
    {
        app('db')->enableQueryLog();

        $this->getFilters();

        foreach (app('db')->getQueryLog() as $query) {
            $this->assertStringContainsString(') as eateries_count from', $query['query']);
        }
    }

    #[Test]
    public function itAppendsTheEateryCountToTheFilterLabel(): void
    {
        $venueType = EateryVenueType::query()->orderBy('venue_type')->firstOrFail();

        $this->assertStringEndsWith(' - (5)', $this->venueTypeFilter($venueType->slug)['label']);
    }

    #[Test]
    public function itDoesntCountEateriesThatArentLive(): void
    {
        $venueType = EateryVenueType::query()->orderBy('venue_type')->firstOrFail();

        Eatery::query()->firstOrFail()->updateQuietly(['live' => false]);

        $this->assertStringEndsWith(' - (4)', $this->venueTypeFilter($venueType->slug)['label']);
    }

    #[Test]
    public function itThrowsWhenTheCountyHasntBeenSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('County not set');

        app(GetFiltersForNationwide::class)->handle();
    }

    protected function venueTypeFilter(string $slug): array
    {
        return collect($this->getFilters()['venueTypes'])->firstOrFail('value', $slug);
    }

    protected function getFilters(array $filters = []): array
    {
        return app(GetFiltersForNationwide::class)->setCounty($this->county)->handle($filters);
    }
}
