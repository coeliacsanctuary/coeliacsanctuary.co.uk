<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesFromQueryBuilderConfigurationAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesFromQueryBuilderConfigurationAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use RuntimeException;

class GetEateriesForMagicRoutePipeline
{
    protected ?GetEateriesPipelineData $rawDto = null;

    /**
     * @param  array{categories: string[]|null, features: string[]|null, venueTypes: string[]|null, towns:
     *     string[]|null, counties: string[]|null}  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return Collection<int, Eatery>
     */
    public function run(Configuration $configuration, array $filters = []): Collection
    {
        $pipes = [
            GetEateriesFromQueryBuilderConfigurationAction::class,
            GetNationwideBranchesFromQueryBuilderConfigurationAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            configuration: $configuration,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        $this->rawDto = $pipelineData;

        /** @var Collection<int, Eatery> $hydratedEateries */
        $hydratedEateries = $pipeline->hydrated;

        return $hydratedEateries;
    }

    public function rawData(): GetEateriesPipelineData
    {
        if ( ! $this->rawDto) {
            throw new RuntimeException('Pipeline not ran');
        }

        return $this->rawDto;
    }
}
