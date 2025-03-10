<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;

class HydrateBranchesActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 1;

    #[Test]
    public function itReturnsTheHydratedBranches(): void
    {
        $hydratedBranches = $this->callHydrateBranchesAction();

        $this->assertInstanceOf(Collection::class, $hydratedBranches->hydratedBranches);
        $this->assertInstanceOf(NationwideBranch::class, $hydratedBranches->hydratedBranches->first());
    }
}
