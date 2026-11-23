<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetEateriesPipeline;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class GetEateriesPipelineTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence): array => ['id' => $sequence->index + 1])
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'venue_type_id' => EateryVenueType::query()->first()->id,
            ]);

        EateryFeature::query()->first()->eateries()->attach(Eatery::query()->first());

        $this->county->eateries->each(function (Eatery $eatery, $index): void {
            $this->build(EateryReview::class)
                ->count(10 - $index)
                ->create([
                    'wheretoeat_id' => $eatery->id,
                    'rating' => random_int(1, 5),
                    'approved' => true,
                ]);
        });

        $this->build(NationwideBranch::class)
            ->count(10)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ]);
    }

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesInTownAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInTownAction::class);
        $this->expectPipelineToExecute(SortPendingEateriesAction::class);
        $this->expectPipelineToExecute(PaginateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateBranchesAction::class);
        $this->expectPipelineToExecute(CheckForMissingEateriesAction::class);
        $this->expectPipelineToExecute(RelateEateriesAndBranchesAction::class);
        $this->expectPipelineToExecute(SerialiseResultsAction::class);

        $this->runPipeline(GetEateriesPipeline::class, $this->town, []);
    }
}
