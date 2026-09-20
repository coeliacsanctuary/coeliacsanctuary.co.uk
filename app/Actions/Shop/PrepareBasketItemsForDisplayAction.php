<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use App\Support\Helpers;
use Illuminate\Support\Collection;
use Money\Money;

class PrepareBasketItemsForDisplayAction
{
    public function handle(ShopOrder $basket): array
    {
        $items = app(GetOrderItemsAction::class)->handle($basket);
        $digitalItems = app(GetOrderDigitalItemsAction::class)->handle($basket);

        /** @var Collection<int, ShopOrderItemResource> $itemCollection */
        $itemCollection = $items->collection;

        /** @var Collection<int, ShopOrderDigitalItemResource> $digitalItemCollection */
        $digitalItemCollection = $digitalItems->collection;

        /** @var int $itemSubtotal */
        $itemSubtotal = $itemCollection->sum(fn (ShopOrderItemResource $item) => ($item->product_price * $item->quantity) + $item->product_add_on_price);
        $digitalItemSubtotal = $digitalItemCollection->sum(fn (ShopOrderDigitalItemResource $item) => $item->digital_card_price);

        return [
            'items' => $items,
            'digitalItems' => $digitalItems,
            'subtotal' => Helpers::formatMoney(Money::GBP($itemSubtotal + $digitalItemSubtotal)),
        ];
    }
}
