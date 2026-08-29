<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryCounty */
class NearbyCountyResource extends JsonResource
{
    /** @return array{name: string, link: string, image: string} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->county,
            'link' => $this->link(),
            'image' => $this->image,
        ];
    }
}
