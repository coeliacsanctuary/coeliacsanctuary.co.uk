<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Closure;
use Illuminate\Support\Collection;
use RuntimeException;

class CheckForMissingEateriesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ($pipelineData->paginator instanceof LengthAwarePaginator) {
            $items = collect($pipelineData->paginator->items());
        } elseif ($pipelineData->eateries instanceof Collection) {
            $items = $pipelineData->eateries;
        } else {
            throw new RuntimeException('No eateries');
        }

        /** @var Collection<int, Eatery> $hydratedEateries */
        $hydratedEateries = $pipelineData->hydrated;

        if ($items->count() === $hydratedEateries->count()) {
            return $next($pipelineData);
        }

        $items->each(function (PendingEatery $pendingEatery, int $index) use (&$hydratedEateries): void {
            if ($hydratedEateries->get($index)?->id === $pendingEatery->id) {
                return;
            }

            /** @var Eatery $duplicateEatery */
            /** @phpstan-ignore-next-line  */
            $duplicateEatery = clone $hydratedEateries->firstWhere('id', $pendingEatery->id);

            $oldEateries = clone $hydratedEateries;

            $hydratedEateries = $oldEateries->take($index);
            $hydratedEateries->push($duplicateEatery);
            $hydratedEateries->push(...$oldEateries->skip($index)->all());
        });

        $pipelineData->hydrated = $hydratedEateries;

        return $next($pipelineData);
    }
}
