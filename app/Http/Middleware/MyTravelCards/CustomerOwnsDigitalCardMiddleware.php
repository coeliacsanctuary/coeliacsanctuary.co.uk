<?php

declare(strict_types=1);

namespace App\Http\Middleware\MyTravelCards;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerOwnsDigitalCardMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var ShopDigitalCard $card */
        $card = $request->route('card');

        /** @var ShopCustomer $customer */
        $customer = $request->user();

        if ($customer->ownedDigitalItems()->where('digital_card_id', $card->id)->doesntExist()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
