<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\AppleWallet;

use App\Actions\Shop\MyTravelCards\GetOrCreateAppleWalletPassAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GetController
{
    public function __invoke(
        Request $request,
        ShopDigitalCard $card,
        GetOrCreateAppleWalletPassAction $getOrCreateAppleWalletPassAction,
    ): MobilePass {
        /** @var ShopCustomer $customer */
        $customer = $request->user();

        return $getOrCreateAppleWalletPassAction->handle($customer, $card);
    }
}
