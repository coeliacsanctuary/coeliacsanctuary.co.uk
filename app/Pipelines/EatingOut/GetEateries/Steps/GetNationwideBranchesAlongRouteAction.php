<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Builders\EatingOut\EateryBuilder;
use App\Builders\EatingOut\NationwideBranchBuilder;
use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\DataObjects\EatingOut\TripRoute;
use App\DataObjects\EatingOut\TripRouteChunk;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Helpers;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GetNationwideBranchesAlongRouteAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->route instanceof TripRoute || ! $pipelineData->routeRadius) {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Route'));

            return $next($pipelineData);
        }

        $radius = Helpers::milesToMeters($pipelineData->routeRadius);
        $chunks = $pipelineData->route->chunks($radius);

        $venueType = DB::raw('(select venue_type_id from wheretoeat where wheretoeat.id = wheretoeat_nationwide_branches.wheretoeat_id) as venue_type_id');

        $query = NationwideBranch::query()
            ->select(['id', 'wheretoeat_id', 'lat', 'lng', $venueType])
            ->where(function (NationwideBranchBuilder $query) use ($chunks): void {
                $chunks->each(fn (TripRouteChunk $chunk) => $query->orWhere(fn (NationwideBranchBuilder $query) => $query
                    ->whereRaw('cast(lat as decimal(10, 7)) between ? and ?', array_values($chunk->latBounds))
                    ->whereRaw('cast(lng as decimal(10, 7)) between ? and ?', array_values($chunk->lngBounds))));
            })
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

        /** @var EloquentCollection<int, NationwideBranch> $branches */
        $branches = $query->get();

        $pendingEateries = $branches
            ->map(fn (NationwideBranch $branch): array => [$branch, $pipelineData->route->locate($chunks, (float) $branch->lat, (float) $branch->lng)])
            ->filter(fn (array $located): bool => $located[1]['distance'] <= $radius)
            ->map(fn (array $located): PendingEatery => new PendingEatery(
                id: $located[0]->wheretoeat_id,
                branchId: $located[0]->id,
                ordering: $located[1]['along'],
                lat: $located[0]->lat,
                lng: $located[0]->lng,
                distance: $located[1]['distance'],
                typeId: EateryType::EATERY,
                /** @phpstan-ignore property.notFound */
                venueTypeId: $located[0]->venue_type_id,
                durationAlongRoute: $located[1]['duration'],
            ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }
}
