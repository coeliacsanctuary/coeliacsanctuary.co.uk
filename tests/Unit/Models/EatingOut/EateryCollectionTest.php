<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Jobs\EatingOut\CalculateEateryCollectionEateryCountsJob;
use App\Jobs\SyncEateryCollectionAndBlogJob;
use App\Models\EatingOut\EateryCollection;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

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
    public function itDispatchesTheCountsJobWhenSaved(): void
    {
        $this->create(EateryCollection::class);

        Bus::assertDispatched(CalculateEateryCollectionEateryCountsJob::class);
    }

    #[Test]
    public function itDispatchesTheSyncJobWhenSaved(): void
    {
        $this->create(EateryCollection::class);

        Bus::assertDispatched(SyncEateryCollectionAndBlogJob::class);
    }

    #[Test]
    public function itCanGetACollectionsUrl(): void
    {
        $eateryCollection = $this->create(EateryCollection::class);

        $this->assertEquals("/eating-out/collections/{$eateryCollection->slug}", $eateryCollection->link);
    }
}
