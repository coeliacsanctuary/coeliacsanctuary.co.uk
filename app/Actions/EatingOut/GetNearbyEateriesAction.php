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
use Illuminate\Support\Str;
use InvalidArgumentException;

class GetNearbyEateriesAction
{
    /** @return Collection<int, array<string, mixed>> */
    public function handle(Eatery|NationwideBranch|LatLng $location, float $miles = 0.5, int $limit = 4): Collection
    {
        /** @var LatLng $latLng */
        $latLng = match ($location::class) {
            Eatery::class, NationwideBranch::class => new LatLng($location->lat, $location->lng),
            default => $location,
        };

        $eateries = $this->getNearbyRecords(
            Eatery::class,
            $latLng,
            $miles,
            $limit,
            $location instanceof Eatery ? $location->id : null,
        );

        $branches = $this->getNearbyRecords(
            NationwideBranch::class,
            $latLng,
            $miles,
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
                'info' => Str::limit($location->info ?? $location->eatery?->info, 120),
                'link' => $location->link(),
                'distance' => Helpers::metersToMiles((float) $location->distance),
                'ratings_count' => $location->reviews_count,
                'average_rating' => $location->average_rating,
            ])
            ->take($limit)
            ->sortBy('distance')
            ->values();

        return $nearbyEateries;
    }

    /**
     * @template T of Eatery | NationwideBranch
     *
     * @param  class-string<T>  $model
     * @return Collection<int, T>
     */
    protected function getNearbyRecords(string $model, LatLng $latLng, float $miles, int $limit, ?int $except = null): Collection
    {
        $columns = match ($model) {
            Eatery::class => ['area_id', 'town_id', 'country_id', 'address', 'slug', 'info'],
            NationwideBranch::class => ['wheretoeat_id', 'area_id', 'town_id', 'country_id', 'county_id', 'address', 'slug'],
            default => throw new InvalidArgumentException("Unsupported model {{$model}}")
        };

        $relations = match ($model) {
            Eatery::class => ['area', 'town', 'county', 'country', 'reviews'],
            NationwideBranch::class => ['eatery', 'area', 'town', 'county', 'country', 'reviews'],
            default => throw new InvalidArgumentException("Unsupported model {{$model}}")
        };

        /** @phpstan-ignore-next-line  */
        return $model::databaseSearchAroundLatLng($latLng, Helpers::milesToMeters($miles), $columns)
            /** @phpstan-ignore-next-line  */
            ->when($model === Eatery::class, fn (Builder $query) => $query->where('type_id', EateryType::EATERY))
            ->when($except, fn (Builder $query) => $query->whereNot('id', $except))
            ->with($relations)
            ->withCount(['reviews'])
            ->reorder()
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
