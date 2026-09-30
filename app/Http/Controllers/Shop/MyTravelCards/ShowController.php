<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards;

use App\Http\Response\Inertia;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Resources\Shop\MyTravelCards\PurchasedCardResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Inertia\Response;

class ShowController
{
    public function __invoke(Inertia $inertia, Request $request, ShopDigitalCard $card): Response
    {
        /** @var ShopCustomer $customer */
        $customer = $request->user();

        /** @var ShopOrder $order */
        $order = $customer
            ->orders()
            ->whereHas('digitalItems', fn ($query) => $query->where('digital_card_id', $card->id))
            ->first();

        return $inertia
            ->doNotTrack()
            ->metaTags([], false)
            ->metaDescription('')
            ->title("{$card->title} - My Travel Cards")
            ->render('Shop/MyTravelCards/Show', [
                'card' => PurchasedCardResource::make($card),
                'id' => md5($order->order_key),
            ]);
    }
}
