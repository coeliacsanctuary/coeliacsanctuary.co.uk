<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use RuntimeException;

class HydrateEateriesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ($pipelineData->paginator) {
            $eateryIds = Arr::map($pipelineData->paginator->items(), fn (PendingEatery $eatery) => $eatery->id);
        } elseif ($pipelineData->eateries) {
            $eateryIds = $pipelineData->eateries->map(fn (PendingEatery $eatery) => $eatery->id)->toArray();
        } else {
            throw new RuntimeException('No eateries');
        }

        $hydratedEateries = Eatery::query()
            ->with([/** @phpstan-ignore-line */
                'country', 'county', 'town', 'town.county', 'type', 'venueType', 'cuisine', 'restaurants',
                'reviews' => function (HasMany $builder) {
                    /** @var HasMany<EateryReview, Eatery> $builder */
                    return $builder
                        ->select(['id', 'wheretoeat_id', 'rating', 'nationwide_branch_id'])
                        ->where('approved', 1)
                        ->latest();
                },
                'features' => function (BelongsToMany $builder) {
                    /** @var BelongsToMany<EateryFeature, Eatery> $builder */
                    return $builder->where('feature', '100% Gluten Free');
                }
            ])
            ->whereIn('id', $eateryIds)
            ->when(count($eateryIds) > 0, fn (Builder $builder) => $builder->orderByRaw('field(id, ' . Arr::join($eateryIds, ',') . ')'))
            ->get();

        $pipelineData->hydrated = $hydratedEateries;

        return $next($pipelineData);
    }
}
