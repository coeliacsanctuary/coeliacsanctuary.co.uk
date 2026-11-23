<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SerialiseBrowseResultsAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $eateries */
        $eateries = $pipelineData->eateries;

        /** @var Collection<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $eateries->map(fn (PendingEatery $eatery): object => new ($pipelineData->jsonResource)($eatery));

        $pipelineData->serialisedEateries = $serialisedEateries;

        return $next($pipelineData);
    }
}
