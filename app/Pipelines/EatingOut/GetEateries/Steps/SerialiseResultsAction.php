<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SerialiseResultsAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, Eatery> $eateries */
        $eateries = $pipelineData->hydrated;

        /** @var Collection<int, PendingEatery> $serialisedEateries */
        $serialisedEateries = $eateries->map(fn (Eatery $eatery) => new ($pipelineData->jsonResource)($eatery));

        if ($pipelineData->paginator) {
            $collection = $serialisedEateries;

            /** @var LengthAwarePaginator<int, JsonResource> $serialisedEateries */
            $serialisedEateries = $pipelineData->paginator->setCollection($collection);
        }

        /** @phpstan-ignore-next-line  */
        $pipelineData->serialisedEateries = $serialisedEateries;

        return $next($pipelineData);
    }
}
