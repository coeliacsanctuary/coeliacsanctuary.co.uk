<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOrderItemsAction
{
    use FormatsDigitalCard;

    public function handle(ShopOrder $order, bool $includeDigitalBundles = true, bool $includeDigitalUpsell = false): AnonymousResourceCollection
    {
        $relations = ['product', 'product.media', 'variant'];

        if ($includeDigitalBundles) {
            $relations = [
                ...$relations,
                'digitalBundles' => fn (Relation $relation) => $relation->with(['card', 'card.price', 'card.product', 'card.product.travelCardSearchTerms']),
            ];
        }

        if ($includeDigitalUpsell) {
            $relations = [
                ...$relations,
                'product.digitalCards', 'product.digitalCards.price',
            ];
        }

        $items = $order
            ->items()
            ->with($relations)
            ->get()
            ->map(function (ShopOrderItem $item) use ($order, $includeDigitalBundles, $includeDigitalUpsell) {
                if ($includeDigitalBundles && $item->digitalBundles->isNotEmpty()) {
                    $item->digitalBundles = $item->digitalBundles->map(function (ShopOrderDigitalItem $digitalBundle) use ($order) {
                        $digitalBundle->card = $this->formatDigitalCard($digitalBundle->card, $order);

                        return $digitalBundle;
                    });
                }

                if ($includeDigitalUpsell && $item->product->digitalCards->isNotEmpty()) {
                    $item->product->digitalCards->map(function (ShopDigitalCard $card) use ($item, $order) {
                        $card->setRelation('product', $item->product);

                        return $this->formatDigitalCard($card, $order);
                    });
                }

                return $item;
            });

        return ShopOrderItemResource::collection($items);
    }
}
