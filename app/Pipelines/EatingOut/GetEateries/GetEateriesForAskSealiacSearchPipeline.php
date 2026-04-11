<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySearchTerm;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInSearchArea;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class GetEateriesForAskSealiacSearchPipeline
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null }  $filters
     * @return Collection<int, Eatery>
     */
    public function run(string $searchTerm, int $range, array $filters, string $sort = 'distance'): Collection
    {
        $pipes = [
            GetEateriesInSearchAreaAction::class,
            GetNationwideBranchesInSearchArea::class,
            SortPendingEateriesAction::class,
            HydrateEateriesAction::class,
            AppendDistanceToEateries::class,
            HydrateBranchesAction::class,
            AppendDistanceToBranches::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            sort: $sort,
            searchTerm: new EaterySearchTerm([
                'term' => $searchTerm,
                'range' => $range,
            ]),
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var Collection<int, Eatery> $eateries */
        $eateries = $pipeline->hydrated;

        return $eateries;
    }
}
