<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop\MyTravelCards;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOneTimeCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['bail', 'required', 'numeric', 'digits:6'],
        ];
    }
}
