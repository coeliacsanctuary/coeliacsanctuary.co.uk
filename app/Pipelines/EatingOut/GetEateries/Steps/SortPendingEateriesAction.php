<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use Closure;
use Illuminate\Support\Collection;

class SortPendingEateriesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $eateries */
        $eateries = $pipelineData->eateries;

        $pipelineData->eateries = $eateries
            ->sort(function (PendingEatery $a, PendingEatery $b) {
                if (is_numeric($a->ordering)) {
                    return $a->ordering <=> $b->ordering;
                }

                return strcmp((string)$a->ordering, (string)$b->ordering);
            })
            ->values();

        return $next($pipelineData);
    }
}
