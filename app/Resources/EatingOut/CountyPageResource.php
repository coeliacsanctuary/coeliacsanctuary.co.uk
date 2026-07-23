<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\ResourceCollections\EatingOut\CountyTownCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class CountyPageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{name: string, slug: string, image: string, towns: CountyTownCollection, eateries: int, reviews: int} */
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
            'description' => $this->formatMarkdown("If you're heading to **{$this->county}**, our eating out guide lists all the gluten free places in the towns, villages, and cities throughout the region. Explore the gluten-free options in **{$this->county}** diverse culinary scene...... Todo - better County intro will go here..."),
            'image' => $this->image ?? $country->image,
            'towns' => new CountyTownCollection($this->activeTowns),
            'eateries' => $this->eateries_count,
            'reviews' => $this->reviews_count,
        ];
    }
}
