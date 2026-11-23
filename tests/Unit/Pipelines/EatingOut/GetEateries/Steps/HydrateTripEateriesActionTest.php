<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateTripEateriesAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HydrateTripEateriesActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    /** @param  Collection<int, PendingEatery>  $pendingEateries */
    protected function callStep(Collection $pendingEateries): GetEateriesPipelineData
    {
        $toReturn = null;

        $this->callAction(HydrateTripEateriesAction::class, new GetEateriesPipelineData(filters: [], eateries: $pendingEateries), function (GetEateriesPipelineData $data) use (&$toReturn): void {
            $toReturn = $data;
        });

        return $toReturn;
    }

    #[Test]
    public function itLoadsEachEateriesNameAndTown(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'Gluten Free Cafe', 'town_id' => 1]);

        $hydrated = $this->callStep(collect([new PendingEatery(id: $eatery->id, branchId: null, ordering: 0)]))->hydrated;

        $this->assertSame('Gluten Free Cafe', $hydrated->get($eatery->id)->name);
        $this->assertSame('Crewe', $hydrated->get($eatery->id)->town->town);
    }

    #[Test]
    public function itLoadsEachBranchesNameTownAndChain(): void
    {
        $chain = $this->create(Eatery::class, ['name' => 'Chain Restaurant']);
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $chain->id, 'name' => 'Crewe Branch', 'town_id' => 1]);

        $hydratedBranches = $this->callStep(collect([new PendingEatery(id: $chain->id, branchId: $branch->id, ordering: 0)]))->hydratedBranches;

        $this->assertSame('Crewe Branch', $hydratedBranches->get($branch->id)->name);
        $this->assertSame('Crewe', $hydratedBranches->get($branch->id)->town->town);
        $this->assertSame('Chain Restaurant', $hydratedBranches->get($branch->id)->eatery->name);
    }

    #[Test]
    public function itCountsAndAveragesEachEateriesReviews(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, ['wheretoeat_id' => $eatery->id, 'rating' => 5, 'approved' => true]);
        $this->create(EateryReview::class, ['wheretoeat_id' => $eatery->id, 'rating' => 2, 'approved' => true]);

        $hydrated = $this->callStep(collect([new PendingEatery(id: $eatery->id, branchId: null, ordering: 0)]))->hydrated->get($eatery->id);

        $this->assertSame(2, $hydrated->reviews_count);
        $this->assertEqualsWithDelta(3.5, (float) $hydrated->reviews_avg_rating, 0.001);
    }

    #[Test]
    public function itCountsAndAveragesEachBranchesOwnReviews(): void
    {
        $chain = $this->create(Eatery::class);
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $chain->id]);
        $otherBranch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $chain->id]);

        $this->create(EateryReview::class, ['wheretoeat_id' => $chain->id, 'nationwide_branch_id' => $branch->id, 'rating' => 4, 'approved' => true]);
        $this->create(EateryReview::class, ['wheretoeat_id' => $chain->id, 'nationwide_branch_id' => $otherBranch->id, 'rating' => 1, 'approved' => true]);

        $hydrated = $this->callStep(collect([new PendingEatery(id: $chain->id, branchId: $branch->id, ordering: 0)]))->hydratedBranches->get($branch->id);

        $this->assertSame(1, $hydrated->reviews_count);
        $this->assertEqualsWithDelta(4.0, (float) $hydrated->reviews_avg_rating, 0.001);
    }

    #[Test]
    public function itGivesNoCountOrAverageWithoutReviews(): void
    {
        $eatery = $this->create(Eatery::class);

        $hydrated = $this->callStep(collect([new PendingEatery(id: $eatery->id, branchId: null, ordering: 0)]))->hydrated->get($eatery->id);

        $this->assertSame(0, $hydrated->reviews_count);
        $this->assertNull($hydrated->reviews_avg_rating);
    }
}
