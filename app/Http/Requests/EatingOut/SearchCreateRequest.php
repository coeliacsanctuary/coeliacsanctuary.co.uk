<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => ['required_without:latlng', 'nullable', 'string', 'min:3', 'max:100'],
            'latlng' => ['required_without:term', 'nullable', 'string', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
            'range' => ['required', 'numeric', 'int', Rule::in([1, 2, 5, 10, 20])],
        ];
    }
}
