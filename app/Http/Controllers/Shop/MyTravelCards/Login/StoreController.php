<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\Login;

use App\Http\Requests\Shop\MyTravelCards\LoginRequest;
use App\Models\Shop\ShopCustomer;
use App\Notifications\Shop\MyTravelCards\OneTimeCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class StoreController
{
    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $customer = ShopCustomer::query()->where('email', $request->string('email')->toString())->firstOrFail();

        $password = Str::password(6, letters: false, symbols: false);
        $customer->update([
            'one_time_code' => $password,
            'one_time_code_expires_at' => now()->addMinutes(15),
        ]);

        $customer->notify(new OneTimeCodeNotification($password, $customer));

        return redirect()
            ->to(route('my-travel-cards.login.confirm.show'))
            ->withCookie('my_travel_cards_email', Crypt::encryptString($request->string('email')->toString()), 15);
    }
}
