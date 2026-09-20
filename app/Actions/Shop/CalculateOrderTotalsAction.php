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
     * @param  Collection<int, ShopOrderDigitalItemResource>  $items
     * @return array{subtotal: int, postage: int, fees: Collection<int, array{fee: int<0, max>, description:
     *     string|null}>, total_fees: int}
     */
    public function handle(Collection $items, Collection $digitalItems, ShopPostageCountry $country): array
    {
        /** @var int $physicalSubtotal */
        $physicalSubtotal = $items->sum(fn (ShopOrderItemResource $item) => $item->product_price * $item->quantity);

        /** @var int $digitalSubtotal */
        $digitalSubtotal = $digitalItems->sum(fn (ShopOrderDigitalItemResource $item) => $item->digital_card_price);

        if($items->isNotEmpty()) {
            $shippingMethod = $items->max(function (ShopOrderItemResource $resource) {
                /** @var ShopProduct $product */
                $product = $resource->product;

                return $product->shipping_method_id;
            });

            $totalWeight = $items->sum(function (ShopOrderItemResource $resource) {
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
                ->firstOr(fn() => throw new RuntimeException('Can not calculate postage'));

            $postage = $postagePrice->price;
        }

        $fees = ShopCustomsFee::query()
            ->where('postage_country_id', $country->id)
            ->get()
            ->map(fn (ShopCustomsFee $fee) => [
                'fee' => $fee->fee,
                'description' => $fee->description,
            ]);

        return [
            'subtotal' => $physicalSubtotal + $digitalSubtotal,
            'fees' => $fees,
            'total_fees' => (int) $fees->sum('fee'),
            'postage' => $postage ?? 0,
        ];
    }
}
