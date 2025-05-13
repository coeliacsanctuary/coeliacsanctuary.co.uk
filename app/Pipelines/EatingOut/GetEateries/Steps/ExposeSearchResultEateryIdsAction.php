<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ExposeSearchResultEateryIdsAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->searchTerm || $pipelineData->searchTerm->term === '') {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Search Term'));

            return $next($pipelineData);
        }

        /** @var Collection<int, PendingEatery> $eateries */
        $eateries = $pipelineData->eateries;

        $eateryIds = $eateries
            ->reject(fn (PendingEatery $eatery) => $eatery->branchId) /** @phpstan-ignore-line */
            ->map(fn (PendingEatery $eatery) => $eatery->id)
            ->toArray();

        $branchIds = $eateries
            ->filter(fn (PendingEatery $eatery) => $eatery->branchId) /** @phpstan-ignore-line */
            ->map(fn (PendingEatery $eatery) => $eatery->branchId)
            ->toArray();

        Cache::remember("search-filters-{$pipelineData->searchTerm->key}", now()->addMinute(), fn () => [
            'eateryIds' => $eateryIds,
            'branchIds' => $branchIds,
        ]);

        return $next($pipelineData);
    }
}
