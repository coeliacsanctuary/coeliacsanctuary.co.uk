<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOrderItemsAction
{
    use FormatsDigitalCard;

    public function handle(ShopOrder $order): AnonymousResourceCollection
    {
        $items = $order
            ->items()
            ->with([
                'product', 'product.media', 'variant',
                'digitalBundles' => fn (Relation $relation) => $relation->with(['card', 'card.price', 'card.product', 'card.product.travelCardSearchTerms']),
            ])
            ->get()
            ->map(function (ShopOrderItem $item) use ($order) {
                if ($item->digitalBundles) {
                    $item->digitalBundles = $item->digitalBundles->map(function (ShopOrderDigitalItem $digitalBundle) use ($order) {
                        $digitalBundle->card = $this->formatDigitalCard($digitalBundle->card, $order);

                        return $digitalBundle;
                    });
                }

                return $item;
            });

        return ShopOrderItemResource::collection($items);
    }
}
