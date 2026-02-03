<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GetEateriesInTownAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->town) {
            throw new RuntimeException('No Town');
        }

        /** @var Builder<Eatery> $query */
        $query = Eatery::query()
            ->when(
                $pipelineData->sort === 'rating',
                fn (Builder $query) => $query->selectRaw('wheretoeat.id, null as branch_id, coalesce((select (round(avg(r.rating) * 2) / 2) + (count(r.rating) * 0.001) from wheretoeat_reviews r where r.approved = 1 and r.wheretoeat_id = wheretoeat.id), 0) as ordering'),
                fn (Builder $query) => $query->selectRaw('wheretoeat.id, null as branch_id, wheretoeat.name as ordering')
            )
            ->where('town_id', $pipelineData->town->id)
            ->where('closed_down', false)
            ->orderBy('ordering');

        if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
            $query = $query->hasCategories($pipelineData->filters['categories']);
        }

        if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
            $query = $query->hasVenueTypes($pipelineData->filters['venueTypes']);
        }

        if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
            $query = $query->hasFeatures($pipelineData->filters['features']);
        }

        /** @var Collection<int, object{id: int, branch_id: int | null, ordering: string}> $pendingEateries */
        $pendingEateries = $query->toBase()->get();

        $pendingEateries = $pendingEateries->map(fn (object $eatery) => new PendingEatery(
            id: $eatery->id,
            branchId: $eatery->branch_id,
            ordering: $eatery->ordering,
        ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }
}
