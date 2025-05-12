<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

class LatLng
{
    public function __construct(public float $lat, public float $lng, public ?string $label = null, public int|float|null $radius = null)
    {
        //
    }

    public function toString(): string
    {
        return "{$this->lat},{$this->lng}";
    }

    public function toLatLng(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
