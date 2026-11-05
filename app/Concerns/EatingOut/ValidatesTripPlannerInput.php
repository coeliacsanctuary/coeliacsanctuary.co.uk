<?php

declare(strict_types=1);

namespace App\Concerns\EatingOut;

use Illuminate\Validation\Rule;

/** @mixin \Illuminate\Foundation\Http\FormRequest */
trait ValidatesTripPlannerInput
{
    protected function tripPlannerRules(): array
    {
        return [
            'points' => ['required', 'array', 'min:2', 'max:7'],
            'points.*.label' => ['required', 'string'],
            'points.*.lat' => ['required', 'numeric'],
            'points.*.lng' => ['required', 'numeric'],
            'distance' => ['required', 'numeric', 'min:1', 'max:10'],
            'stop_interval' => ['nullable', 'integer', Rule::in([60, 90, 120, 180])],
        ];
    }

    public function stopInterval(): ?int
    {
        return $this->filled('stop_interval') ? $this->integer('stop_interval') : null;
    }

    /** @return array<int, array{label: string, lat: float, lng: float}> */
    public function points(): array
    {
        /** @var array<int, array{label: string, lat: float|string, lng: float|string}> $points */
        $points = $this->collect('points')->values()->all();

        return array_map(fn (array $point): array => [
            'label' => (string) $point['label'],
            'lat' => (float) $point['lat'],
            'lng' => (float) $point['lng'],
        ], $points);
    }
}
