<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInCollectionAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInCollectionAction;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class GetEateriesFromCollectionPipeline
{
    /**
     * @return Collection<int, PendingEatery>
     */
    public function run(Configuration $configuration): Collection
    {
        $pipes = [
            GetEateriesInCollectionAction::class,
            GetNationwideBranchesInCollectionAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: ['categories' => null, 'features' => null, 'venueTypes' => null],
            configuration: $configuration,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var Collection<int, PendingEatery> $serialisedEateries */
        $serialisedEateries = $pipeline->eateries;

        return $serialisedEateries;
    }
}
