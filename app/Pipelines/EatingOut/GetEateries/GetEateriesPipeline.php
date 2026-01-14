<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use App\Resources\EatingOut\EateryListResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class GetEateriesPipeline
{
    protected ?GetEateriesPipelineData $pipelineData = null;

    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return LengthAwarePaginator<int, JsonResource>
     */
    public function run(EateryTown $town, array $filters, string $sort = 'alphabetical', string $jsonResource = EateryListResource::class): LengthAwarePaginator
    {
        $pipes = [
            GetEateriesInTownAction::class,
            GetNationwideBranchesInTownAction::class,
            SortPendingEateriesAction::class,
            PaginateEateriesAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
            SerialiseResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            town: $town,
            filters: $filters,
            sort: $sort,
            jsonResource: $jsonResource
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        $this->pipelineData = $pipeline;

        /** @var LengthAwarePaginator<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }

    public function rawData(): GetEateriesPipelineData
    {
        /** @var GetEateriesPipelineData $data */
        $data = $this->pipelineData;

        return $data;
    }
}
