<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut\Api;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class TripPlannerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'points' => ['required', 'array', 'min:2'],
            'points.*.lat' => ['required', 'numeric'],
            'points.*.lng' => ['required', 'numeric'],
            'distance' => ['required', 'numeric', 'min:1', 'max:10'],
        ];
    }

    /** @return Collection<int, LatLng> */
    public function points(): Collection
    {
        /** @var Collection<int, array{lat: float, lng: float}> $points */
        $points = $this->collect('points');

        return $points->map(fn (array $point): LatLng => new LatLng((float) $point['lat'], (float) $point['lng']))->values();
    }
}
