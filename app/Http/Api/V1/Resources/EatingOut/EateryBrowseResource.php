<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\DataObjects\EatingOut\PendingEatery;
use App\Enums\EatingOut\EateryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PendingEatery */
class EateryBrowseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            'key' => $this->id . ($this->branchId ? '-' . $this->branchId : null),
            'id' => $this->id,
            'branchId' => $this->branchId,
            'isNationwideBranch' => $this->branchId !== null,
            'location' => [
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'color' => $this->getColour(),
        ];
    }

    public function getColour(): string
    {
        return EateryType::from((int) $this->typeId)->color();
    }
}
