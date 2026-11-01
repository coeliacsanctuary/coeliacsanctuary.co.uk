<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\GetEateries\GetFilteredEateriesPipeline;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInLatLngRadiusAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInLatLngAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInSearchArea;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class GetFilteredEateriesPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence): array => ['id' => $sequence->index + 1])
            ->create();
    }

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesFromFiltersAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesFromFiltersAction::class);
        $this->expectPipelineToExecute(GetEateriesInSearchAreaAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInSearchArea::class);
        $this->expectPipelineToExecute(GetEateriesInLatLngRadiusAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInLatLngAction::class);
        $this->expectPipelineToExecute(SortPendingEateriesAction::class);
        $this->expectPipelineToExecute(PaginateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateBranchesAction::class);
        $this->expectPipelineToExecute(CheckForMissingEateriesAction::class);
        $this->expectPipelineToExecute(RelateEateriesAndBranchesAction::class);
        $this->expectPipelineToExecute(SerialiseResultsAction::class);

        $this->runPipeline(GetFilteredEateriesPipeline::class, []);
    }
}
