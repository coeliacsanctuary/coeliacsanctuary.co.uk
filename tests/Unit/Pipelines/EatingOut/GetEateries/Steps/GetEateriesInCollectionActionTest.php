<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class GetEateriesInCollectionActionTest extends GetEateriesTestCase
{
    #[Test]
    public function itReturnsTheNextClosureInTheAction(): void
    {
        $this->assertInstanceOf(GetEateriesPipelineData::class, $this->callGetEateriesInCollectionAction());
    }

    #[Test]
    public function itReturnsEachEateryAPendingEatery(): void
    {
        $collection = $this->callGetEateriesInCollectionAction()->eateries;

        $collection->each(fn ($item) => $this->assertInstanceOf(PendingEatery::class, $item));
    }

    #[Test]
    public function itAppendsToThePassedInCollection(): void
    {
        $eateries = new Collection(range(0, 4));

        $newCollection = $this->callGetEateriesInCollectionAction($eateries)->eateries;

        $this->assertCount(10, $newCollection); // 5 in setup, 5 from above
    }

    #[Test]
    public function itDoesntGetEateriesThatAreMarkedAsClosedDown(): void
    {
        Eatery::query()->update(['closed_down' => true]);

        $eateries = $this->callGetEateriesInCollectionAction();

        $this->assertCount(0, $eateries->eateries);
    }
}
