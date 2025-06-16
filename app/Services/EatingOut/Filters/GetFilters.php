<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

use App\Enums\EatingOut\EateryType as EateryTypeEnum;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetFilters
{
    protected array $filters;

    public function handle(array $filters = []): array
    {
        $this->filters = $filters;

        return [
            'categories' => $this->getCategories(),
            'venueTypes' => $this->getVenueTypes(),
            'features' => $this->getFeatures(),
        ];
    }

    /**
     * @param  Builder<EateryType> | Builder<EateryVenueType> | Builder<EateryFeature>  $builder
     * @return Builder<EateryType> | Builder<EateryVenueType> | Builder<EateryFeature>
     */
    protected function withWhereClause(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * @param  Builder<EateryType> | Builder<EateryVenueType> | Builder<EateryFeature>  $builder
     * @return Builder<EateryType> | Builder<EateryVenueType> | Builder<EateryFeature>
     */
    protected function orderBy(Builder $builder, string $field): Builder
    {
        return $builder->orderBy($field);
    }

    /**
     * @template T of EateryType | EateryVenueType | EateryFeature
     *
     * @param  class-string<T>  $filterable
     * @param  null | callable(T $filterable): array  $mergeWithMap
     * @return Collection<int, non-empty-array>
     */
    protected function resolveFilters(string $filterable, string $filterName, string $orderBy, string $nameColumn, string $checkedColumn, ?callable $mergeWithMap = null): Collection
    {
        /** @var Builder<T> $baseQuery */
        $baseQuery = $filterable::query();

        $baseQuery = $this->withWhereClause($baseQuery); /** @phpstan-ignore-line */
        $baseQuery = $this->orderBy($baseQuery, $orderBy);

        $filters = $baseQuery->get();

        return $filters
            ->reject(fn (Model $filter): bool => $filter->hasAttribute('eateries_count') && $filter->eateries_count === 0)
            ->map(fn (Model $filter): array => [
                'value' => (string) $filter->$checkedColumn,
                'label' => $filter->$nameColumn . ($filter->hasAttribute('eateries_count') ? " - ({$filter->eateries_count})" : ''),
                'disabled' => false,
                'checked' => $this->filterIsEnabled($filterName, $filter->$checkedColumn),
                /** @phpstan-ignore-next-line  */
                ...($mergeWithMap ? $mergeWithMap($filter) : []),
            ]);
    }

    /** @return Collection<int, non-empty-array> */
    protected function getCategories(): Collection
    {
        return $this->resolveFilters(EateryType::class, 'categories', 'id', 'name', 'type');
    }

    /** @return Collection<int, non-empty-array> */
    protected function getVenueTypes(): Collection
    {
        return $this
            ->resolveFilters(
                EateryVenueType::class,
                'venueTypes',
                'venue_type',
                'venue_type',
                'slug',
                fn (EateryVenueType $filterable) => ['groupBy' => Str::of(EateryTypeEnum::from($filterable->type_id)->name)->title()->plural()],
            )
            ->sortBy(function (array $filterable) {
                if ($filterable['groupBy'] === 'Eatery') {
                    return 0;
                }

                if ($filterable['groupBy'] === 'Attraction') {
                    return 1;
                }

                return 2;
            });
    }

    /** @return Collection<int, non-empty-array> */
    protected function getFeatures(): Collection
    {
        return $this->resolveFilters(EateryFeature::class, 'features', 'feature', 'feature', 'slug');
    }

    protected function filterIsEnabled(string $key, string $value): bool
    {
        if ( ! Arr::has($this->filters, $key)) {
            return false;
        }

        /** @var string[] $filters */
        $filters = Arr::get($this->filters, $key, []);

        if ( ! $filters) {
            return false;
        }

        return collect($filters)
            ->map(fn (string $filter) => Str::lower($filter))
            ->contains(Str::lower($value));
    }
}
