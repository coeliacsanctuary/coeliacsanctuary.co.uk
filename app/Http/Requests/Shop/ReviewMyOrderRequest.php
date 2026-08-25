<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Rules\Shop\ReviewProductIsInOrderRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewMyOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'whereHeard' => ['array'],
            'whereHeard.*' => ['string', 'max:255'],
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'int', 'bail', 'distinct', 'exists:shop_products,id', new ReviewProductIsInOrderRule()],
            'products.*.rating' => ['required', 'numeric', Rule::in(range(1, 5))],
            'products.*.review' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
