<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExploreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['required', 'string', 'min:3'],
            'sort' => [Rule::in(['distance', 'rating', 'alphabetical'])],
        ];
    }
}
