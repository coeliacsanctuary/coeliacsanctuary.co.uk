<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\GetEateriesPipelineData;

class ExposeSearchResultEateryIdsActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 10;

    protected int $branchesToCreate = 1;

    #[Test]
    public function itReturnsTheNextClosureInTheAction(): void
    {
        $this->assertInstanceOf(GetEateriesPipelineData::class, $this->callExposeResultResultEateryIdsAction());
    }

    #[Test]
    public function itPutsAnItemInTheCacheUsingTheSearchTerm(): void
    {
        $this->assertTrue(Cache::missing("search-filters-{$this->eaterySearchTerm->key}"));

        $this->callExposeResultResultEateryIdsAction();

        $this->assertFalse(Cache::missing("search-filters-{$this->eaterySearchTerm->key}"));
    }

    #[Test]
    public function itStoresKeysOfEateryIdsAndBranchIds(): void
    {
        $this->callExposeResultResultEateryIdsAction();

        $cache = Cache::get("search-filters-{$this->eaterySearchTerm->key}");

        $this->assertIsArray($cache);
        $this->assertArrayHasKeys(['eateryIds', 'branchIds'], $cache);
    }
}
