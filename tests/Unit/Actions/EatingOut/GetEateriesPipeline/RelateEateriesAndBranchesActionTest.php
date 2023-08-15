<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\GetEateriesPipeline;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;

class RelateEateriesAndBranchesActionTest extends GetEateriesTestCase
{
    protected int $eateriesToCreate = 1;

    protected int $branchesToCreate = 1;

    /** @test */
    public function itSortsEateries(): void
    {
        $eatery = Eatery::query()->first();
        $branch = NationwideBranch::query()->first();

        $eateries = collect([new PendingEatery(id: $eatery->id, branchId: $branch->id, ordering: 'abc')]);

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $eateries->paginate(5),
            hydrated: $this->callHydrateEateriesAction($eateries)->hydrated,
            hydratedBranches: $this->callHydrateBranchesAction($eateries)->hydratedBranches
        );

        $result = $this->callRelateEateriesAndBranchesAction($pipelineData);

        $updatedEatery = $result->hydrated->first();

        $this->assertNotNull($updatedEatery->branch);
        $this->assertTrue($updatedEatery->branch->is($branch));
    }
}
