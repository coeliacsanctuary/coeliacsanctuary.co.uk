<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use App\Models\EatingOut\Eatery;
use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{
    public function isNationwide(): bool
    {
        /** @var Eatery $eatery */
        $eatery = $this->route('eatery');

        return $eatery->county?->county === 'Nationwide';
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'review' => ['required', 'string', 'max:1500'],
            'food_rating' => ['nullable', 'in:poor,good,excellent'],
            'service_rating' => ['nullable', 'in:poor,good,excellent'],
            'how_expensive' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'images' => ['array', 'max:6'],
            'images.*' => ['string', 'exists:temporary_file_uploads,id'],
            'method' => ['in:website,app'],
            'admin_review' => ['missing'],
            'branch_id' => $this->isNationwide() ? ['nullable', 'int', 'required_without:branch_name'] : ['prohibited'],
            'branch_name' => $this->isNationwide() ? ['required_without:branch_id', 'nullable', 'string'] : ['prohibited'],
        ];
    }
}
