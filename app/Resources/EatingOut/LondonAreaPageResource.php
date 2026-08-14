<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Concerns\FormatsMarkdown;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Stringable;

/** @mixin EateryArea */
class LondonAreaPageResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{name: string, description: string|Stringable, slug: string, image: string, latlng: string|null, borough: LondonBoroughResource} */
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
            'description' => $this->formatMarkdown($this->description ?? $this->defaultDescription()),
            'slug' => $this->slug,
            'image' => $this->image ?? $borough->image ?? $county->image ?? $country->image,
            'latlng' => $this->latlng,
            'borough' => new LondonBoroughResource($borough),
        ];
    }
}
