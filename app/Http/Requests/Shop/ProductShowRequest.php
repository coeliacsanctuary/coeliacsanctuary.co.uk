<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductShowRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reviewFilter' => Rule::in([0.5,1,1.5,2,2.5,3,3.5,4,4.5,5]),
        ];
    }
}
