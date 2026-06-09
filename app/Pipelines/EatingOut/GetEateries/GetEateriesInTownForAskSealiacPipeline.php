<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class GetEateriesInTownForAskSealiacPipeline
{
    protected ?GetEateriesPipelineData $pipelineData = null;

    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null }  $filters
     * @return Collection<int, Eatery>
     */
    public function run(EateryTown $town, array $filters, string $sort = 'alphabetical'): Collection
    {
        $pipes = [
            GetEateriesInTownAction::class,
            GetNationwideBranchesInTownAction::class,
            SortPendingEateriesAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            town: $town,
            filters: $filters,
            sort: $sort,
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
