<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryTown */
class TownPageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{name: string, slug: string, image: string, county: TownCountyResource} */
    public function toArray(Request $request)
    {
        /** @var EateryCounty $county */
        $county = $this->county;

        /** @var EateryCountry $country */
        $country = $county->country;

        return [
            'name' => $this->town,
            'description' => $this->formatMarkdown($this->description ?? $this->defaultDescription()),
            'slug' => $this->slug,
            'image' => $this->image ?? $county->image ?? $country->image,
            'latlng' => $this->latlng,
            'county' => new TownCountyResource($county),
        ];
    }
}
