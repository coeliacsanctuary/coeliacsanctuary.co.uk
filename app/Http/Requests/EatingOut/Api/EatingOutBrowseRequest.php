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
            'filter.category' => ['array'],
            'filter.category.*' => ['string', Rule::in(['wte', 'att', 'hotel'])],
            'filter.venueType' => ['array'],
            'filter.venueType.*' => ['string', Rule::exists(EateryVenueType::class, 'slug')],
            'filter.feature' => ['array'],
            'filter.feature.*' => ['string', Rule::exists(EateryFeature::class, 'slug')],
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

    /** @return array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null } */
    public function filters(): array
    {
        return [
            'categories' => $this->has('filter.category') ? $this->array('filter.category') : null,
            'venueTypes' => $this->has('filter.venueType') ? $this->array('filter.venueType') : null,
            'features' => $this->has('filter.feature') ? $this->array('filter.feature') : null,
            'county' => null,
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('range')) {
            $this->merge([
                'radius' => $this->integer('range'),
            ]);
        }

        if ($this->array('filter.category')) {
            $this->merge([
                'filter' => [
                    'category' => $this->string('filter.category')->explode(',')->toArray(),
                ],
            ]);
        }

        if ($this->array('filter.feature')) {
            $this->merge([
                'filter' => [
                    'feature' => $this->string('filter.feature')->explode(',')->toArray(),
                ],
            ]);
        }

        if ($this->array('filter.venueType')) {
            $this->merge([
                'filter' => [
                    'venueType' => $this->string('filter.venueType')
                        ->explode(',')
                        ->map(function (mixed $venueType) {
                            if (is_numeric($venueType)) {
                                return EateryVenueType::query()->findOrFail($venueType)->slug;
                            }

                            return $venueType;
                        })
                        ->toArray(),
                ],
            ]);
        }
    }
}
