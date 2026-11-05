<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;

class TripPlannerIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    /** @return array{label: string, lat: float, lng: float} | null */
    public function destination(): ?array
    {
        if ( ! $this->filled('to')) {
            return null;
        }

        $encoded = strtr($this->string('to')->toString(), '-_', '+/');
        $decoded = base64_decode(str_pad($encoded, (int) ceil(mb_strlen($encoded) / 4) * 4, '='), true);

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

        return ['label' => $destination['label'], 'lat' => $lat, 'lng' => $lng];
    }
}
