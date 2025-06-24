<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryArea */
class LondonAreaPageResource extends JsonResource
{
    /** @return array{name: string, slug: string, image: string, borough: LondonBoroughResource} */
    public function toArray(Request $request)
    {
        /** @var EateryTown $borough */
        $borough = $this->town;

        /** @var EateryCounty $county */
        $county = $borough->county;

        /** @var EateryCountry $country */
        $country = $county->country;

        return [
            'name' => $this->area,
            'slug' => $this->slug,
            'image' => $this->image ?? $borough->image ?? $county->image ?? $country->image,
            'latlng' => $this->latlng,
            'borough' => new LondonBoroughResource($borough),
        ];
    }
}
