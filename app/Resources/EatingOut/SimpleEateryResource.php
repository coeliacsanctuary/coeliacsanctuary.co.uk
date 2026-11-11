<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleEateryResource extends JsonResource
{
    public function eateryName(Eatery|NationwideBranch $resource): string
    {
        if ($resource instanceof Eatery) {
            return $resource->name;
        }

        /** @var Eatery $eatery */
        $eatery = $resource->eatery;

        if ( ! $resource->name) {
            return $eatery->name;
        }

        return "{$resource->name} - {$eatery->name}";
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        /** @var Eatery | NationwideBranch $resource */
        $resource = $this->resource;

        return [
            'name' => $this->eateryName($resource),
            'link' => $resource->link(),
            'location' => [
                'name' => $resource->town?->slug === 'nationwide' ? $resource->short_location : $resource->full_location,
                'link' => $resource->town?->link(),
            ],
            'address' => collect(explode("\n", $resource->address))
                ->map(fn (string $line): string => mb_trim($line))
                ->join(', '),
            'created_at' => $resource->created_at?->diffForHumans(),
        ];
    }
}
