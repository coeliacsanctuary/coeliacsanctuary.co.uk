<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Exceptions\QuantityException;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;

class AlterItemQuantityAction
{
    /** @param $action 'increase' | 'decrease' */
    public function handle(ShopOrderItem $orderItem, string $mode): void
    {
        /** @var ShopProductVariant $variant */
        $variant = $orderItem->variant;

        if ($mode === 'increase' && $variant->quantity < 1) {
            throw QuantityException::notEnoughAvailable();
        }

        /** @var ShopOrder $basket */
        $basket = $orderItem->order;

        $basket->touch();

        if ($mode === 'increase') {
            $orderItem->increment('quantity');

            if ($variant->variant_type === ProductVariantType::PHYSICAL) {
                $variant->decrement('quantity');
            }

            if ($variant->variant_type === ProductVariantType::BUNDLE) {
                /** @var ShopProduct $product */
                $product = $orderItem->product;

                /** @var ShopProductVariant $physicalVariant */
                $physicalVariant = $product->variants->where('variant_type', ProductVariantType::PHYSICAL)->first();

                $physicalVariant->decrement('quantity');
            }

            app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

            return;
        }

        $orderItem->decrement('quantity');

        if ($variant->variant_type === ProductVariantType::PHYSICAL) {
            $variant->increment('quantity');
        }

        if ($variant->variant_type === ProductVariantType::BUNDLE) {
            /** @var ShopProduct $product */
            $product = $orderItem->product;

            /** @var ShopProductVariant $physicalVariant */
            $physicalVariant = $product->variants->where('variant_type', ProductVariantType::PHYSICAL)->first();

            $physicalVariant->increment('quantity');
        }

        if ($orderItem->quantity === 0) {
            $orderItem->delete();
        }

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);
    }
}
