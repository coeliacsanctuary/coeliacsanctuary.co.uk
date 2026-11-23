<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\Login\Order;

use App\Actions\Shop\MyTravelCards\CheckIfCustomerCanAccessMyTravelCardsAction;
use App\Actions\Shop\MyTravelCards\LoginCustomerAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Illuminate\Http\RedirectResponse;

class GetController
{
    public function __invoke(
        ShopOrder $order,
        CheckIfCustomerCanAccessMyTravelCardsAction $canAccessMyTravelCardsAction,
        LoginCustomerAction $loginCustomerAction,
    ): RedirectResponse {
        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        if ($canAccessMyTravelCardsAction->handle($customer) === false) {
            return redirect()
                ->route('my-travel-cards.login.show')
                ->withErrors(['email' => 'Sorry, you must have purchased a digital card to access My Travel Cards.']);
        }

        $loginCustomerAction->handle($customer);

        return redirect()->route('my-travel-cards.index');
    }
}
