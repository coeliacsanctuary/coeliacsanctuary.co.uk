<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInCollectionAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInCollectionAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Resources\EatingOut\EateryListResource;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class GetEateriesFromCollectionPipeline
{
    /**
     * @param  class-string<JsonResource>  $jsonResource
     * @return LengthAwarePaginator<int, JsonResource>
     */
    public function run(Configuration $configuration, string $jsonResource = EateryListResource::class): LengthAwarePaginator
    {
        $pipes = [
            GetEateriesInCollectionAction::class,
            GetNationwideBranchesInCollectionAction::class,
            PaginateEateriesAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
            SerialiseResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            configuration: $configuration,
            jsonResource: $jsonResource
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var LengthAwarePaginator<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }
}
