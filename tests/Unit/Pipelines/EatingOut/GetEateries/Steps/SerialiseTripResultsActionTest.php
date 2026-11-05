<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseTripResultsAction;
use App\Resources\EatingOut\EateryTripResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SerialiseTripResultsActionTest extends TestCase
{
    #[Test]
    public function itSerialisesEachPendingEateryInOrderWithItsHydratedModel(): void
    {
        $eatery = new Eatery()->forceFill(['id' => 1, 'name' => 'Independent Cafe']);
        $branch = new NationwideBranch()->forceFill(['id' => 20, 'wheretoeat_id' => 2, 'name' => 'Chain Branch']);

        $pipelineData = new GetEateriesPipelineData(
            filters: [],
            eateries: collect([
                new PendingEatery(id: 2, branchId: 20, ordering: 100, typeId: 1),
                new PendingEatery(id: 1, branchId: null, ordering: 200, typeId: 1),
            ]),
            hydrated: collect([1 => $eatery]),
            hydratedBranches: collect([20 => $branch]),
        );

        $toReturn = null;

        $this->callAction(SerialiseTripResultsAction::class, $pipelineData, function (GetEateriesPipelineData $data) use (&$toReturn): void {
            $toReturn = $data;
        });

        $serialised = $toReturn->serialisedEateries;

        $this->assertContainsOnlyInstancesOf(EateryTripResource::class, $serialised);
        $this->assertSame(['Chain Branch', 'Independent Cafe'], $serialised->map(fn (EateryTripResource $resource): ?string => $resource->toArray(new Request())['name'])->all());
    }
}
