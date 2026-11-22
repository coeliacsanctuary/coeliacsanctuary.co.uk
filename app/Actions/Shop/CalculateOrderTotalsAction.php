<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopCustomsFee;
use App\Models\Shop\ShopPostageCountry;
use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Support\Collection;
use RuntimeException;

class CalculateOrderTotalsAction
{
    /**
     * @param  Collection<int, ShopOrderItemResource>  $items
     * @param  Collection<int, ShopOrderDigitalItemResource>  $digitalItems
     * @return array{subtotal: int, postage: int}
     */
    public function handle(Collection $items, Collection $digitalItems, ShopPostageCountry $country): array
    {
        throw_if($country->country !== 'United Kingdom', new RuntimeException('Only postage to the UK is supported'));

        /** @var int $physicalSubtotal */
        $physicalSubtotal = $items->sum(fn (ShopOrderItemResource $item): int => $item->product_price * $item->quantity);

        /** @var int $digitalSubtotal */
        $digitalSubtotal = $digitalItems->sum(fn (ShopOrderDigitalItemResource $item) => $item->digital_card_price);

        if ($items->isNotEmpty()) {
            $shippingMethod = $items->max(function (ShopOrderItemResource $resource) {
                /** @var ShopProduct $product */
                $product = $resource->product;

                return $product->shipping_method_id;
            });

            $totalWeight = $items->sum(function (ShopOrderItemResource $resource): int {
                /** @var ShopProductVariant $variant */
                $variant = $resource->variant;

                return $variant->weight * $resource->quantity;
            });

            /** @var ShopPostagePrice $postagePrice */
            $postagePrice = ShopPostagePrice::query()
                ->where('postage_country_area_id', $country->postage_area_id)
                ->where('shipping_method_id', $shippingMethod)
                ->where('max_weight', '>=', $totalWeight)
                ->orderBy('max_weight')
                ->firstOr(fn () => throw new RuntimeException('Can not calculate postage'));

            $postage = $postagePrice->price;
        }

        return [
            'subtotal' => $physicalSubtotal + $digitalSubtotal,
            'postage' => $postage ?? 0,
        ];
    }
}
