<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesFromQueryBuilderConfigurationAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesFromQueryBuilderConfigurationAction;
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
use Illuminate\Support\Collection;

class GetEateriesForMagicRoutePipeline
{
    /**
     * @param  array{categories: string[]|null, features: string[]|null, venueTypes: string[]|null, towns:
     *     string[]|null, counties: string[]|null}  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return Collection<int, JsonResource>
     */
    public function run(Configuration $configuration, array $filters, string $jsonResource = EateryListResource::class): Collection
    {
        $pipes = [
            GetEateriesFromQueryBuilderConfigurationAction::class,
            GetNationwideBranchesFromQueryBuilderConfigurationAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
            SerialiseResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            configuration: $configuration,
            jsonResource: $jsonResource,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var Collection<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }
}
