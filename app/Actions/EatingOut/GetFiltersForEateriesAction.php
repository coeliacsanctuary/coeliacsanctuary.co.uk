<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class GetFiltersForEateriesAction
{
    protected ?Closure $whereClause;

    protected array $filters;

    protected bool $withCount;

    public function handle(?Closure $whereClause = null, array $filters = [], bool $withCount = true): array
    {
        if ($withCount && $whereClause === null) {
            throw new RuntimeException('Missing where clause');
        }

        $this->filters = $filters;

        $this->whereClause = $whereClause;

        $this->withCount = $withCount;

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
    protected function queryOrderClause(Builder $builder, string $field): Builder
    {
        if ($this->withCount && $this->whereClause) {
            return $builder
                ->withCount(['eateries' => fn (Builder $query) => call_user_func($this->whereClause, $query)])
                ->orderByRaw("eateries_count desc, {$field} asc");
        }

        return $builder->orderBy($field);
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
    protected function getCategories(): Collection
    {
        /** @var Builder<EateryType> $baseQuery */
        $baseQuery = EateryType::query();

        $baseQuery = $this->queryOrderClause($baseQuery, 'name');

        /** @var Collection<int, EateryType> $categories */
        $categories = $baseQuery->get();

        return $categories
            ->reject(fn (EateryType $type): bool => $type->eateries_count === 0)
            ->map(fn (EateryType $type): array => [
                'value' => $type->type,
                'label' => $type->name . ($this->withCount ? " - ({$type->eateries_count})" : ''),
                'disabled' => false,
                'checked' => $this->filterIsEnabled('categories', $type->type),
            ]);
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
    protected function getVenueTypes(): Collection
    {
        /** @var Builder<EateryVenueType> $baseQuery */
        $baseQuery = EateryVenueType::query();

        $baseQuery = $this->queryOrderClause($baseQuery, 'venue_type');

        /** @var Collection<int, EateryVenueType> $venueTypes */
        $venueTypes = $baseQuery->get();

        return $venueTypes
            ->reject(fn (EateryVenueType $type): bool => $type->eateries_count === 0)
            ->map(fn (EateryVenueType $venueType): array => [
                'value' => $venueType->slug,
                'label' => $venueType->venue_type . ($this->withCount ? " - ({$venueType->eateries_count})" : ''),
                'disabled' => false,
                'checked' => $this->filterIsEnabled('venueTypes', $venueType->slug),
            ]);
    }

    /** @return Collection<int, array{value: string, label: string, disabled: bool, checked: bool}> */
    protected function getFeatures(): Collection
    {
        /** @var Builder<EateryFeature> $baseQuery */
        $baseQuery = EateryFeature::query();

        $baseQuery = $this->queryOrderClause($baseQuery, 'feature');

        /** @var Collection<int, EateryFeature> $features */
        $features = $baseQuery->get();

        return $features
            ->reject(fn (EateryFeature $type): bool => $type->eateries_count === 0)
            ->map(fn (EateryFeature $feature): array => [
                'value' => $feature->slug,
                'label' => $feature->feature . ($this->withCount ? " - ({$feature->eateries_count})" : ''),
                'disabled' => false,
                'checked' => $this->filterIsEnabled('features', $feature->slug),
            ]);
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
