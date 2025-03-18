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

    /** @return array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null } */
    public function filters(): array
    {
        return [
            'categories' => $this->has('filter.category') ? $this->array('filter.category') : null,
            'venueTypes' => $this->has('filter.venueTypes') ? explode(',', $this->string('filter.venueTypes')->toString()) : null,
            'features' => $this->has('filter.features') ? explode(',', $this->string('filter.features')->toString()) : null,
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

        if($this->array('filter.category')) {
            $this->merge([
                'filter' => [
                    'category' => $this->string('filter.category')->explode(',')->toArray(),
                ]
            ]);
        }

        if ($this->has('filter.venueType')) {
            $this->merge([
                'filter' => array_merge(
                    $this->collect('filter')->forget('venueType')->toArray(),
                    [
                        'venueTypes' => EateryVenueType::query()
                            ->where('id', $this->integer('filter.venueType'))
                            ->first()
                            ?->slug,
                    ]
                ),
            ]);
        }
    }
}
