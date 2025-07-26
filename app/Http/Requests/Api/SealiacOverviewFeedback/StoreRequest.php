<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\SealiacOverviewFeedback;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => [Rule::in(['up', 'down'])],
        ];
    }
}
