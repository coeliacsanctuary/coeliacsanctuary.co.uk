<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Services\EatingOut\LocationSearchService;
use App\Support\State\EatingOut\Search\LatLngState;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GetNationwideBranchesInSearchArea implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->searchTerm || $pipelineData->searchTerm->term === '') {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Search Term'));

            return $next($pipelineData);
        }

        try {
            $latLng = $this->getLatLng($pipelineData);
        } catch (Exception) {
            return $next($pipelineData);
        }

        /** @var Collection<int, NationwideBranch> $ids */
        $ids = NationwideBranch::algoliaSearchAroundLatLng($latLng, $pipelineData->searchTerm->range)->get();

        $ids = $ids->each(function (NationwideBranch $result): void {
            if (isset($result->scoutMetadata()['_rankingInfo']['geoDistance'])) {
                $distance = round($result->scoutMetadata()['_rankingInfo']['geoDistance'] / 1609, 1);

                $result->distance = $distance;
            }
        })
            ->sortBy('distance')
            ->values();

        /** @var Builder<Eatery> $query */
        $query = NationwideBranch::query()
            /** @lang mysql */
            ->selectRaw(Arr::join([
                'wheretoeat.id as id',
                'wheretoeat_nationwide_branches.id as branch_id',
                'if(wheretoeat_nationwide_branches.name = "", concat(wheretoeat.name, "-", wheretoeat.id), concat(wheretoeat_nationwide_branches.name, " ", wheretoeat.name)) as ordering',
            ], ','))
            ->whereIn('wheretoeat_nationwide_branches.id', $ids->pluck('id'))
            ->join('wheretoeat', 'wheretoeat.id', 'wheretoeat_nationwide_branches.wheretoeat_id')
            ->whereHas('eatery', function (Builder $query) use ($pipelineData) {
                /** @var Builder<Eatery> $query */
                /** @phpstan-ignore-next-line  */
                if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
                    $query = $query->hasCategories($pipelineData->filters['categories']);
                }

                if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
                    $query = $query->hasVenueTypes($pipelineData->filters['venueTypes']);
                }

                if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
                    $query = $query->hasFeatures($pipelineData->filters['features']);
                }

                return $query;
            });

        /** @var Collection<int, object{id: int, branch_id: int | null, ordering: string}> $pendingEateries */
        $pendingEateries = $query->toBase()->get();

        $pendingEateries = $pendingEateries->map(fn (object $eatery) => new PendingEatery(
            id: $eatery->id,
            branchId: $eatery->branch_id,
            ordering: $ids->firstWhere('id', $eatery->branch_id)?->distance ? (string) $ids->firstWhere('id', $eatery->branch_id)->distance : $eatery->ordering,
            distance: (float) $ids->firstWhere('id', $eatery->branch_id)?->distance,
        ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }

    protected function getLatLng(GetEateriesPipelineData $pipelineData): \App\DataObjects\EatingOut\LatLng
    {
        if (LatLngState::$latLng) {
            return LatLngState::$latLng;
        }

        /** @phpstan-ignore-next-line  */
        $latLng = app(LocationSearchService::class)->getLatLng($pipelineData->searchTerm->term);

        LatLngState::$latLng = $latLng;

        return $latLng;
    }
}
