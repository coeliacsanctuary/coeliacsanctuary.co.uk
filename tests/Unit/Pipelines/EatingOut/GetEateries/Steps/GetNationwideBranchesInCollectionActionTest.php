<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class GetNationwideBranchesInCollectionActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 1;

    #[Test]
    public function itReturnsTheNextClosureInTheAction(): void
    {
        $this->assertInstanceOf(GetEateriesPipelineData::class, $this->callGetBranchesInCollectionAction());
    }

    #[Test]
    public function itReturnsEachEateryAPendingEatery(): void
    {
        $collection = $this->callGetBranchesInCollectionAction()->eateries;

        $collection->each(fn ($item) => $this->assertInstanceOf(PendingEatery::class, $item));
    }

    #[Test]
    public function itAppendsToThePassedInCollection(): void
    {
        $eateries = new Collection(range(0, 4));

        $newCollection = $this->callGetBranchesInCollectionAction($eateries);

        $this->assertCount(10, $newCollection->eateries); // 5 in setup, 5 from above
    }

    #[Test]
    public function itDoesntGetEateriesThatAreMarkedAsClosedDown(): void
    {
        Eatery::query()->update(['closed_down' => true]);

        $eateries = $this->callGetBranchesInCollectionAction();

        $this->assertCount(0, $eateries->eateries);
    }
}
