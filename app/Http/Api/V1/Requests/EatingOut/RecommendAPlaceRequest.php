<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use App\Models\EatingOut\EateryVenueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendAPlaceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'place' => ['required', 'array'],
            'place.name' => ['required', 'string'],
            'place.location' => ['required', 'string'],
            'place.url' => ['bail', 'nullable', 'url'],
            'place.venueType' => ['bail', 'nullable', 'numeric', 'int', Rule::exists(EateryVenueType::class, 'id')],
            'place.details' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'place.name' => 'place name',
            'place.location' => 'place location / address',
            'place.url' => 'place web address',
            'place.venueType' => 'place venue type',
            'place.details' => 'place details',
            'name' => 'your name',
            'email' => 'your email address',
        ];
    }

    public function messages()
    {
        return [
            'place.url' => 'Make sure the place web address starts with http:// or https://',
        ];
    }
}
