<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\LatLng;
use App\Builders\EatingOut\EateryBuilder;
use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Helpers;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use RuntimeException;

class GetNationwideBranchesInLatLngAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->latLng instanceof LatLng || ! $pipelineData->latLng->radius) {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Lat/Lng'));

            return $next($pipelineData);
        }

        /** A branch has no venue type of its own, it lives on the eatery it belongs to. */
        $venueType = DB::raw('(select venue_type_id from wheretoeat where wheretoeat.id = wheretoeat_nationwide_branches.wheretoeat_id) as venue_type_id');

        $idQuery = NationwideBranch::databaseSearchAroundLatLng($pipelineData->latLng, $pipelineData->isMeters ? $pipelineData->latLng->radius : Helpers::milesToMeters($pipelineData->latLng->radius), [$venueType])
            ->whereHas('eatery', function (EateryBuilder $query) use ($pipelineData): EateryBuilder {
                $query->where('closed_down', false);
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

        /** @var EloquentCollection<int, NationwideBranch> $eateries */
        $eateries = $idQuery->get();

        $pendingEateries = $eateries->map(fn (NationwideBranch $eatery): PendingEatery => new PendingEatery(
            id: $eatery->wheretoeat_id,
            branchId: $eatery->id,
            ordering: (float)$eatery->distance,
            lat: $eatery->lat,
            lng: $eatery->lng,
            distance: (float)$eatery->distance,
            typeId: EateryType::EATERY,
            /** @phpstan-ignore property.notFound */
            venueTypeId: $eatery->venue_type_id,
        ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }
}
