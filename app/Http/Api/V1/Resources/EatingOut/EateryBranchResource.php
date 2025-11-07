<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NationwideBranch */ class EateryBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name !== '' ? $this->name : $this->eatery->name,
            'full_name' => $this->full_name,
            'county' => $this->county?->county,
            'town' => $this->town?->town,
            'area' => $this->area->area ?? null,
            'location' => [
                'address' => collect(explode("\n", $this->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'distance' => $this->distance,
        ];
    }
}
