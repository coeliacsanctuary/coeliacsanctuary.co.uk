<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\ResourceCollections\EatingOut\NationwideListCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class NationwidePageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @var array{categories: string[] | null, venueTypes: string[] | null, features: string[] | null} */
    protected array $filters = ['categories' => null, 'venueTypes' => null, 'features' => null];

    /** @param  array{categories: string[] | null, venueTypes: string[] | null, features: string[] | null}  $filters */
    public function withFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /** @return array{name: string, slug: string, description: string, chains: NationwideListCollection, eateries: int, reviews: int} */
    public function toArray(Request $request)
    {
        $this->load([
            'eateries' => fn (HasMany $builder) => $builder
                ->when($this->filters['categories'], fn (Builder $builder, array $categories) => $builder->hasCategories($categories))
                ->when($this->filters['venueTypes'], fn (Builder $builder, array $venueTypes) => $builder->hasVenueTypes($venueTypes))
                ->when($this->filters['features'], fn (Builder $builder, array $features) => $builder->hasFeatures($features))
                ->orderBy('name'),
            'eateries.features' => function (BelongsToMany $builder) {
                /** @var BelongsToMany<EateryFeature, Eatery> $builder */
                return $builder->where('feature', '100% Gluten Free');
            },
            'eateries.venueType', 'eateries.type', 'eateries.cuisine', 'eateries.reviews', 'eateries.restaurants',
        ]);
        $this->loadCount(['reviews', 'eateries']);

        return [
            'name' => $this->county,
            'slug' => $this->slug,
            'description' => $this->formatMarkdown($this->description ?? $this->defaultNationwideDescription()),
            'chains' => new NationwideListCollection($this->eateries),
            'eateries' => $this->eateries_count,
            'reviews' => $this->reviews_count,
        ];
    }
}
