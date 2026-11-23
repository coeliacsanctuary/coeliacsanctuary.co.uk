<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopCustomer;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Auth;

class LoginCustomerAction
{
    public function handle(ShopCustomer $customer): void
    {
        Auth::guard('customers')
            ->setRememberDuration((int) CarbonInterval::days(7)->totalMinutes)
            ->login($customer, true);

        $customer->update([
            'one_time_code' => null,
            'one_time_code_expires_at' => null,
        ]);
    }
}
