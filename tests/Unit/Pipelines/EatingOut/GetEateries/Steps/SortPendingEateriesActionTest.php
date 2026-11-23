<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\PendingEatery;

class SortPendingEateriesActionTest extends GetEateriesTestCase
{
    #[Test]
    public function itSortsEateries(): void
    {
        $eateries = $this->callGetEateriesInTownAction()->eateries;
        $eateries = $this->callGetBranchesAction($eateries)->eateries;

        $sortedEateries = $this->callSortEateriesAction($eateries)->eateries;

        $eateryNames = $sortedEateries->map(fn (PendingEatery $eatery): string|float => $eatery->ordering)->toArray();

        $this->assertSortedAlphabetically($eateryNames);
    }
}
