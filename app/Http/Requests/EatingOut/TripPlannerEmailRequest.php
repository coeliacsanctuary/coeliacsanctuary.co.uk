<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;

class TripPlannerEmailRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
