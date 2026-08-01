<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetNearbyEateriesAction
{
    /** @return Collection<int, array<string, mixed>> */
    public function handle(Eatery|NationwideBranch|LatLng $location, int $limit = 4): Collection
    {
        /** @var LatLng $latLng */
        $latLng = match ($location::class) {
            Eatery::class, NationwideBranch::class => new LatLng($location->lat, $location->lng),
            default => $location,
        };

        $eateries = $this->getNearbyRecords(
            Eatery::class,
            $latLng,
            $limit,
            $location instanceof Eatery ? $location->id : null,
        );

        $branches = $this->getNearbyRecords(
            NationwideBranch::class,
            $latLng,
            $limit,
            $location instanceof NationwideBranch ? $location->id : null,
        );

        /** @var Collection<int, array<string, mixed>> $nearbyEateries */
        $nearbyEateries = collect([...$eateries, ...$branches])
            ->map(fn (Eatery|NationwideBranch $location) => [
                'id' => $location instanceof NationwideBranch ? "{$location->wheretoeat_id}-{$location->id}" : $location->id,
                'name' => $location->name ?? $location->eatery?->name,
                'address' => collect(explode("\n", $location->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'info' => $location->display_snippet ?? $location->eatery->display_snippet,
                'link' => $location->link(),
                'distance' => Helpers::metersToMiles((float) $location->distance),
                'ratings_count' => $location->reviews_count,
                'average_rating' => $location->average_rating,
            ])
            ->sortBy('distance')
            ->take($limit)
            ->values();

        return $nearbyEateries;
    }

    /**
     * @template T of Eatery | NationwideBranch
     *
     * @param  class-string<T>  $model
     * @return Collection<int, T>
     */
    protected function getNearbyRecords(string $model, LatLng $latLng, int $limit, ?int $except = null): Collection
    {
        $columns = match ($model) {
            Eatery::class => ['area_id', 'town_id', 'country_id', 'address', 'slug', 'snippet', 'info'],
            NationwideBranch::class => ['wheretoeat_id', 'area_id', 'town_id', 'country_id', 'county_id', 'address', 'slug'],
            default => throw new InvalidArgumentException("Unsupported model {{$model}}"),
        };

        $relations = match ($model) {
            Eatery::class => ['area', 'town', 'county', 'country', 'reviews'],
            NationwideBranch::class => ['eatery', 'area', 'town', 'county', 'country', 'reviews'],
        };

        /** @phpstan-ignore-next-line  */
        return $model::databaseSearchAroundLatLng($latLng, Helpers::milesToMeters(9999), $columns)
            ->when($model === Eatery::class, fn (Builder $query) => $query->where('type_id', EateryType::EATERY))
            ->when($except, fn (Builder $query) => $query->whereNot('id', $except))
            ->notNationwide()
            ->with($relations)
            ->withCount(['reviews'])
            ->reorder()
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }
}
