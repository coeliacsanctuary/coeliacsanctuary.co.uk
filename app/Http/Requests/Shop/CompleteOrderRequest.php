<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Rules\Shop\ValidPostcodeRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'contact' => ['required', 'array'],
            'contact.name' => ['required', 'string'],
            'contact.email' => ['required', 'string', 'email', 'confirmed'],
            'contact.phone' => ['nullable'],
            'contact.subscribeToNewsletter' => ['nullable', 'boolean'],
            //
            'shipping' => ['required', 'array'],
            'shipping.address_1' => ['required', 'string'],
            'shipping.address_2' => ['nullable', 'string'],
            'shipping.address_3' => ['nullable', 'string'],
            'shipping.town' => ['required', 'string'],
            'shipping.county' => ['nullable', 'string'],
            'shipping.postcode' => ['required', 'string', 'bail', new ValidPostcodeRule()],
        ];
    }

    public function attributes()
    {
        return [
            'contact.name' => 'name',
            'contact.email' => 'email address',
            'shipping.address_1' => 'address',
            'shipping.town' => 'town',
            'shipping.postcode' => 'postcode',
        ];
    }
}
