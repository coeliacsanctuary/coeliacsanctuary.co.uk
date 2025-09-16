<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPostageCountry;
use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Support\Collection;
use RuntimeException;

class CalculateOrderTotalsAction
{
    /**
     * @param  Collection<int, ShopOrderItemResource>  $items
     * @return array{subtotal: int, postage: int}
     */
    public function handle(ShopOrder $basket, Collection $items, ShopPostageCountry $country): array
    {
        /** @var int $subtotal */
        $subtotal = $items->map(fn (ShopOrderItemResource $item) => $item->product_price * $item->quantity)->sum();

        $postage = $basket->is_digital_only ? 0 : $this->calculatePostagePrice($items, $country);

        return [
            'subtotal' => $subtotal,
            'postage' => $postage,
        ];
    }

    /**
     * @param Collection<int, ShopOrderItemResource> $items
     */
    protected function calculatePostagePrice(Collection $items, ShopPostageCountry $country): int
    {
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
            ->firstOr(fn () => throw new RuntimeException('Can not calculate postage'));

        return $postagePrice->price;
    }
}
