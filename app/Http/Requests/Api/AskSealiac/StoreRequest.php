<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\AskSealiac;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'chatId' => ['required', 'string', 'min:8', 'max:8'],
            'prompt' => ['required', 'string', 'min:3', 'max:500'],
            'messages' => ['array', 'max:50'],
            'messages.*.role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'messages.*.message' => ['required', 'string'],
        ];
    }
}
