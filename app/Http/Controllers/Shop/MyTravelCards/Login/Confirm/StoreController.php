<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\Login\Confirm;

use App\Actions\Shop\MyTravelCards\LoginCustomerAction;
use App\Http\Requests\Shop\MyTravelCards\ConfirmOneTimeCodeRequest;
use App\Models\Shop\ShopCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StoreController
{
    public function __invoke(ConfirmOneTimeCodeRequest $request, LoginCustomerAction $loginCustomerAction): RedirectResponse
    {
        $email = Crypt::decryptString($request->cookie('my_travel_cards_email'));

        $customer = ShopCustomer::query()
            ->where('email', $email)
            ->firstOrFail();

        if ($customer->one_time_code_expires_at->isPast()) {
            return redirect()
                ->to(route('my-travel-cards.login.show'))
                ->withErrors(['email' => 'Sorry, your one time code has expired, please try again.']);
        }

        if ( ! Hash::check($request->string('code')->toString(), $customer->one_time_code)) {
            throw ValidationException::withMessages([
                'code' => 'The one time code you entered is incorrect, please try again.',
            ]);
        }

        $loginCustomerAction->handle($customer);

        return redirect()
            ->route('my-travel-cards.index')
            ->withoutCookie('my_travel_cards_email');
    }
}
