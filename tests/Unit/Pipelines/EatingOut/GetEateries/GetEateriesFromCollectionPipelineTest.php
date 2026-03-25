<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use App\Pipelines\EatingOut\GetEateries\GetEateriesFromCollectionPipeline;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInCollectionAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInCollectionAction;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class GetEateriesFromCollectionPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $eateries = $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence) => ['id' => $sequence->index + 1])
            ->create([
                'venue_type_id' => EateryVenueType::query()->first()->id,
            ]);

        EateryFeature::query()->first()->eateries()->attach(Eatery::query()->first());

        $eateries->each(function (Eatery $eatery, $index): void {
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
            ->create();
    }

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesInCollectionAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInCollectionAction::class);
        $this->expectPipelineToExecute(PaginateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateBranchesAction::class);
        $this->expectPipelineToExecute(CheckForMissingEateriesAction::class);
        $this->expectPipelineToExecute(RelateEateriesAndBranchesAction::class);
        $this->expectPipelineToExecute(SerialiseResultsAction::class);

        $this->runPipeline(GetEateriesFromCollectionPipeline::class, new Configuration(), []);
    }
}
