<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

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
     * @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}>
     */
    protected function resolveFilters(string $filterable, string $filterName, string $orderBy, string $nameColumn, string $checkedColumn): Collection
    {
        /** @var Builder<T> $baseQuery */
        $baseQuery = $filterable::query();

        $baseQuery = $this->withWhereClause($baseQuery); /** @phpstan-ignore-line */
        $baseQuery = $this->orderBy($baseQuery, $orderBy);

        $filters = $baseQuery->get();

        return $filters
            ->reject(fn (Model $filter): bool => $filter->eateries_count === 0)
            ->map(fn (Model $filter): array => [
                'value' => (string) $filter->$checkedColumn,
                'label' => $filter->$nameColumn . ($filter->eateries_count ? " - ({$filter->eateries_count})" : ''),
                'disabled' => false,
                'checked' => $this->filterIsEnabled($filterName, $filter->$checkedColumn),
            ]);
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
    protected function getCategories(): Collection
    {
        return $this->resolveFilters(EateryType::class, 'categories', 'id', 'name', 'type');
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
    protected function getVenueTypes(): Collection
    {
        return $this->resolveFilters(EateryVenueType::class, 'venueTypes', 'venue_type', 'venue_type', 'slug');
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
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
