<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\EateryCollection;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCollectionTest extends TestCase
{
    #[Test]
    public function itReturnsTheConfigurationValueAsAConfigurationObject(): void
    {
        $eateryCollection = $this->create(EateryCollection::class);

        $this->assertInstanceOf(Configuration::class, $eateryCollection->configuration);
    }

    #[Test]
    public function itReturnsAConfiguredConfiguration(): void
    {
        $eateryCollection = $this->create(EateryCollection::class, [
            'configuration' => ['wheres' => [['foo', '=', 'baz']]],
        ]);

        $this->assertInstanceOf(Configuration::class, $eateryCollection->configuration);

        $this->assertNotEmpty($eateryCollection->configuration->getWheres());
        $this->assertCount(1, $eateryCollection->configuration->getWheres());
        $this->assertEquals(new Where('foo', '=', 'baz'), $eateryCollection->configuration->getWheres()->first());
    }

    #[Test]
    public function itSetsTheEateryQueryWhenSaving(): void
    {
        $eateryCollection = $this->create(EateryCollection::class);

        $this->assertNotNull($eateryCollection->eatery_query);
        $this->assertNotEmpty($eateryCollection->eatery_query);

        $this->assertStringContainsString('select `wheretoeat`.`id`', $eateryCollection->eatery_query);
        $this->assertStringContainsString('from `wheretoeat`', $eateryCollection->eatery_query);
    }

    #[Test]
    public function itSetsTheBranchQueryWhenSaving(): void
    {
        $eateryCollection = $this->create(EateryCollection::class);

        $this->assertNotNull($eateryCollection->branch_query);
        $this->assertNotEmpty($eateryCollection->branch_query);

        $this->assertStringContainsString('select `wheretoeat`.`id` as `id`, `wheretoeat_nationwide_branches`.`id` as `branch_id`', $eateryCollection->branch_query);
        $this->assertStringContainsString('from `wheretoeat_nationwide_branches`', $eateryCollection->branch_query);
    }

    #[Test]
    public function itCanGetACollectionsUrl(): void
    {
        $eateryCollection = $this->create(EateryCollection::class);

        $this->assertEquals("/eating-out/collections/{$eateryCollection->slug}", $eateryCollection->link);
    }
}
