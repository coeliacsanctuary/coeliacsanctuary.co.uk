<?php

declare(strict_types=1);

namespace App\Rules\Shop;

use App\Actions\Shop\MyTravelCards\CheckIfCustomerCanAccessMyTravelCardsAction;
use App\Models\Shop\ShopCustomer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerHasPurchasedDigitalCardsRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $customer = ShopCustomer::query()->where('email', $value)->firstOrFail();

        if (app(CheckIfCustomerCanAccessMyTravelCardsAction::class)->handle($customer) === false) {
            $fail('Sorry, you must have purchased a digital card to access My Travel Cards.');
        }
    }
}
