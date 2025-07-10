<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SealiacOverviewRatingStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => [Rule::in(['up', 'down'])],
        ];
    }
}
