<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\GoogleWallet;

use App\Actions\Shop\MyTravelCards\GetOrCreateGoogleWalletPassAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GetController
{
    public function __invoke(
        Request $request,
        ShopDigitalCard $card,
        GetOrCreateGoogleWalletPassAction $getOrCreateGoogleWalletPassAction,
    ): MobilePass {
        /** @var ShopCustomer $customer */
        $customer = $request->user();

        return $getOrCreateGoogleWalletPassAction->handle($customer, $card);
    }
}
