<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut\Api;

use Illuminate\Foundation\Http\FormRequest;

class LookupGetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sessionToken' => ['required', 'string'],
        ];
    }
}
