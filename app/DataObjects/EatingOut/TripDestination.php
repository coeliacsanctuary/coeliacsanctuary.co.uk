<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

final readonly class TripDestination
{
    public function __construct(
        public string $label,
        public float $lat,
        public float $lng,
    ) {
    }

    public static function fromQueryValue(string $value): ?self
    {
        $encoded = strtr($value, '-_', '+/');
        $decoded = base64_decode(mb_str_pad($encoded, (int) ceil(mb_strlen($encoded) / 4) * 4, '='), true);

        /** @var mixed $destination */
        $destination = $decoded !== false ? json_decode($decoded, true) : null;

        if ( ! is_array($destination) || ! is_string($destination['label'] ?? null) || ! is_numeric($destination['lat'] ?? null) || ! is_numeric($destination['lng'] ?? null)) {
            return null;
        }

        $lat = (float) $destination['lat'];
        $lng = (float) $destination['lng'];

        if ($destination['label'] === '' || mb_strlen($destination['label']) > 255 || abs($lat) > 90 || abs($lng) > 180) {
            return null;
        }

        return new self($destination['label'], $lat, $lng);
    }

    public function toQueryValue(): string
    {
        return mb_rtrim(strtr(base64_encode((string) json_encode($this->toArray())), '+/', '-_'), '=');
    }

    public function url(): string
    {
        return route('eating-out.trip-planner', ['to' => $this->toQueryValue()]);
    }

    /** @return array{label: string, lat: float, lng: float} */
    public function toArray(): array
    {
        return ['label' => $this->label, 'lat' => $this->lat, 'lng' => $this->lng];
    }
}
