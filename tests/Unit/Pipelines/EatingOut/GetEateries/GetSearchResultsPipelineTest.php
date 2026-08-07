<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInSearchArea;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetSearchResultsPipelineTest extends TestCase
{
    protected EaterySearchTerm $eaterySearchTerm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        Http::preventStrayRequests();
        $london = ['lat' => 51.50, 'lon' => 0.12, 'display_name' => 'London', 'type' => 'administrative'];
        $edinburgh = ['lat' => 55.95, 'lon' => -3.18, 'display_name' => 'Edinburgh', 'type' => 'administrative'];

        Eatery::query()->update([
            'lat' => $london['lat'],
            'lng' => $london['lon'],
        ]);

        Http::fake(['*' => Http::response([$london, $edinburgh])]);

        $this->eaterySearchTerm = $this->create(EaterySearchTerm::class, [
            'term' => 'London',
        ]);

        $eateries = $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence) => ['id' => $sequence->index + 1])
            ->create([
                'venue_type_id' => EateryVenueType::query()->first()->id,
                'lat' => $london['lat'],
                'lng' => $london['lon'],
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
            ->create([
                'lat' => $london['lat'],
                'lng' => $london['lon'],
            ]);

    }

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesInSearchAreaAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInSearchArea::class);
        $this->expectPipelineToExecute(SortPendingEateriesAction::class);
        $this->expectPipelineToExecute(PaginateEateriesAction::class);
        $this->expectPipelineToExecute(HydrateEateriesAction::class);
        $this->expectPipelineToExecute(AppendDistanceToEateries::class);
        $this->expectPipelineToExecute(HydrateBranchesAction::class);
        $this->expectPipelineToExecute(AppendDistanceToBranches::class);
        $this->expectPipelineToExecute(CheckForMissingEateriesAction::class);
        $this->expectPipelineToExecute(RelateEateriesAndBranchesAction::class);
        $this->expectPipelineToExecute(SerialiseResultsAction::class);

        $this->runPipeline(GetSearchResultsPipeline::class, $this->eaterySearchTerm, []);
    }
}
