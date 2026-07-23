<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\ResourceCollections\EatingOut\LondonBoroughCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class LondonPageResource extends JsonResource
{
    use FormatsMarkdown;

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
            'description' => $this->formatMarkdown("If you're heading to **London**, our eating out guide lists all the gluten free places in the towns, villages, and cities throughout the region. Explore the gluten-free options in **London** diverse culinary scene...... Todo - better County intro will go here..."),
            'image' => $this->image ?? $country->image,
            'boroughs' => new LondonBoroughCollection($this->activeTowns),
            'eateries' => $this->eateries_count,
            'reviews' => $this->reviews_count,
        ];
    }
}
