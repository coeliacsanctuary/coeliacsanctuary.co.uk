<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrder;
use App\Rules\Shop\ValidPostcodeRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = $this->contactRules();

        if ($this->basket()->is_digital_only === false) {
            $rules = array_merge($rules, $this->shippingRules());
        }

        return $rules;
    }

    protected function contactRules(): array
    {
        return [
            'contact' => ['required', 'array'],
            'contact.name' => ['required', 'string'],
            'contact.email' => ['required', 'string', 'email'],
            'contact.phone' => ['nullable'],
            'contact.subscribeToNewsletter' => ['nullable', 'boolean'],
        ];
    }

    protected function shippingRules(): array
    {
        return [
            'shipping' => ['required', 'array'],
            'shipping.address_1' => ['required', 'string'],
            'shipping.address_2' => ['nullable', 'string'],
            'shipping.address_3' => ['nullable', 'string'],
            'shipping.town' => ['required', 'string'],
            'shipping.county' => ['nullable', 'string'],
            'shipping.postcode' => ['required', 'string', 'bail', new ValidPostcodeRule()],
        ];
    }

    public function basket(): ShopOrder
    {
        return once(function () {
            /** @var string $token */
            $token = $this->cookie('basket_token');

            /** @var ShopOrder $basket */
            $basket = app(ResolveBasketAction::class)->handle($token, false);

            return $basket;
        });
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
