<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\ResourceCollections\EatingOut\CountyTownCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Stringable;

/** @mixin EateryCounty */
class CountyPageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{name: string, slug: string, latlng: string|null, description: string|Stringable, image: string, towns: CountyTownCollection, eateries: int, reviews: int} */
    public function toArray(Request $request)
    {
        $this->load('activeTowns', 'activeTowns.county', 'activeTowns.liveEateries', 'activeTowns.liveBranches', 'activeTowns.liveEateries.area', 'activeTowns.liveBranches.area');
        $this->loadCount(['eateries', 'reviews']);

        /** @var EateryCountry $country */
        $country = $this->country;

        return [
            'name' => $this->county,
            'slug' => $this->slug,
            'latlng' => $this->latlng,
            'description' => $this->formatMarkdown((string) $this->description),
            'image' => $this->image ?? $country->image,
            'towns' => new CountyTownCollection($this->activeTowns),
            'eateries' => $this->eateries_count,
            'reviews' => $this->reviews_count,
        ];
    }
}
