<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\ResourceCollections\EatingOut\CountyTownCollection;
use App\ResourceCollections\EatingOut\LondonBoroughCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class LondonPageResource extends JsonResource
{
    /** @return array{name: string, slug: string, latlng: string, image: string, boroughs: LondonBoroughCollection, eateries: int, reviews: int} */
    public function toArray(Request $request)
    {
        $this->load([
            'activeTowns', 'activeTowns.county', 'activeTowns.liveEateries', 'activeTowns.liveBranches',
            /** @phpstan-ignore-next-line  */
            'activeTowns.areas' => fn (Relation $builder) => $builder->chaperone()->withCount('eateries'),
        ]);
        $this->loadCount(['eateries', 'reviews']);

        /** @var EateryCountry $country */
        $country = $this->country;

        return [
            'name' => $this->county,
            'slug' => $this->slug,
            'latlng' => (string)$this->latlng,
            'image' => $this->image ?? $country->image,
            'boroughs' => new LondonBoroughCollection($this->activeTowns),
            'eateries' => $this->eateries_count,
            'reviews' => $this->reviews_count,
        ];
    }
}
