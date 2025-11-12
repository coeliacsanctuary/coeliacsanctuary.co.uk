<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;

class GeocodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => ['required', 'string'],
        ];
    }
}
