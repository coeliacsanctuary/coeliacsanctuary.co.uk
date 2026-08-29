<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NationwideBranch */
class NationwideBranchResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?: $this->eatery->name,
            'county' => [
                'id' => $this->county_id,
                'name' => $this->county?->county,
                'link' => $this->county?->link(),
            ],
            'town' => [
                'id' => $this->town_id,
                'name' => $this->town?->town,
                'link' => $this->town?->link(),
            ],
            'area' => $this->area ? [
                'id' => $this->area_id,
                'name' => $this->area->area,
                'link' => $this->area->link(),
            ] : null,
            'link' => $this->link(),
            'location' => [
                'address' => collect(explode("\n", $this->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
        ];
    }
}
