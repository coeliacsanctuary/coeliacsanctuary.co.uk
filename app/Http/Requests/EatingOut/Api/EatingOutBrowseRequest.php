<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut\Api;

use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EatingOutBrowseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'radius' => ['required', 'numeric'],
            'filter' => ['array'],
            'filter.category' => ['string', Rule::in(['wte', 'att', 'hotel'])],
            'filter.venueTypes' => ['string', Rule::exists(EateryVenueType::class, 'slug')],
            'filter.features' => ['string', Rule::exists(EateryFeature::class, 'slug')],
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

    /** @return array{categories: string[] | null, features: string[] | null, venueTypes: string[] | null} */
    public function filters(): array
    {
        return [
            'categories' => $this->has('filter.category') ? explode(',', $this->string('filter.category')->toString()) : null,
            'venueTypes' => $this->has('filter.venueType') ? explode(',', $this->string('filter.venueType')->toString()) : null,
            'features' => $this->has('filter.feature') ? explode(',', $this->string('filter.feature')->toString()) : null,
        ];
    }
}
