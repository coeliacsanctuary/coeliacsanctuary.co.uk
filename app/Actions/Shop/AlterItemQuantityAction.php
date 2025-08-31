<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Exceptions\QuantityException;
use App\Models\Shop\ShopOrderItem;
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

        $orderItem->order?->touch();

        if ($mode === 'increase') {
            $orderItem->increment('quantity');

            if($variant->variant_type !== ProductVariantType::DIGITAL) {
                $variant->decrement('quantity');
            }

            app(CheckIfBasketHasDigitalProductsAction::class)->handle($orderItem->order);

            return;
        }

        $orderItem->decrement('quantity');

        if($variant->variant_type !== ProductVariantType::DIGITAL) {
            $variant->increment('quantity');
        }

        if ($orderItem->quantity === 0) {
            $orderItem->delete();
        }

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($orderItem->order);
    }
}
