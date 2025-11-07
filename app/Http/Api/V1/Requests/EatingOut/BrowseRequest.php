<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Foundation\Http\FormRequest;

class BrowseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'radius' => ['required', 'numeric'],
        ];
    }

    public function latLng(): LatLng
    {
        return new LatLng(
            lat: $this->float('lat'),
            lng: $this->float('lng'),
            radius: $this->float('radius'),
        );
    }

    /** @return array{categories: non-empty-list<string>|null, venueTypes: non-empty-list<string>|null, features: non-empty-list<string>|null} */
    public function filters(): array
    {
        return [
            'categories' => $this->filled('filter.category') ? explode(',', $this->string('filter.category')->toString()) : null,
            'venueTypes' => $this->filled('filter.venueType') ? explode(',', $this->string('filter.venueType')->toString()) : null,
            'features' => $this->filled('filter.feature') ? explode(',', $this->string('filter.feature')->toString()) : null,
        ];
    }
}
