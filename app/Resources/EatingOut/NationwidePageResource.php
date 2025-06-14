<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\ResourceCollections\EatingOut\NationwideListCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class NationwidePageResource extends JsonResource
{
    /** @return array{name: string, slug: string, chains: NationwideListCollection, eateries: int, reviews: int} */
    public function toArray(Request $request)
    {
        $this->load([
            'eateries' => fn (HasMany $builder) => $builder->orderBy('name'),
            'eateries.features' => function (BelongsToMany $builder) {
                /** @var BelongsToMany<EateryFeature, Eatery> $builder */
                return $builder->where('feature', '100% Gluten Free');
            },
            'eateries.venueType', 'eateries.type', 'eateries.cuisine', 'eateries.reviews',
        ]);
        $this->loadCount(['reviews']);

        return [
            'name' => $this->county,
            'slug' => $this->slug,
            'chains' => new NationwideListCollection($this->eateries),
            'eateries' => $this->eateries->count(),
            'reviews' => $this->reviews_count,
        ];
    }
}
