<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOrderDigitalItemsAction
{
    use FormatsDigitalCard;

    public function handle(ShopOrder $order): AnonymousResourceCollection
    {
        $items = $order
            ->digitalItems()
            ->with(['card', 'card.price', 'card.product', 'card.product.travelCardSearchTerms'])
            ->get()
            ->map(function (ShopOrderDigitalItem $item) use ($order) {
                $item->card = $this->formatDigitalCard($item->card, $order);

                return $item;
            });

        return ShopOrderDigitalItemResource::collection($items);
    }
}
