<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SealiacOverviewFeedbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => [Rule::in(['up', 'down'])],
        ];
    }
}
