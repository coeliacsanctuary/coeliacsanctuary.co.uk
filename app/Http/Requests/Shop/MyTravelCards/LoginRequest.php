<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop\MyTravelCards;

use App\Models\Shop\ShopCustomer;
use App\Rules\Shop\CustomerHasPurchasedDigitalCardsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email', Rule::exists(ShopCustomer::class, 'email'), new CustomerHasPurchasedDigitalCardsRule()],
        ];
    }

    public function messages()
    {
        return [
            'email.exists' => 'Sorry, you must have purchased a digital card to use access My Travel Cards.',
        ];
    }
}
