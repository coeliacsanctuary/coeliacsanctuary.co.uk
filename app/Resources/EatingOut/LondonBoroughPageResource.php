<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\ResourceCollections\EatingOut\LondonBoroughAreaCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryTown */
class LondonBoroughPageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{name: string, slug: string, image: string, county: TownCountyResource, areas: LondonBoroughAreaCollection, intro_text: string} */
    public function toArray(Request $request)
    {
        /** @var EateryCounty $county */
        $county = $this->county;

        /** @var EateryCountry $country */
        $country = $county->country;

        return [
            'name' => $this->town,
            'slug' => $this->slug,
            'image' => $this->image ?? $county->image ?? $country->image,
            'latlng' => $this->latlng,
            'county' => new TownCountyResource($county),
            'areas' => new LondonBoroughAreaCollection($this->areas),
            'intro_text' => $this->formatMarkdown((string) $this->description),
        ];
    }
}
