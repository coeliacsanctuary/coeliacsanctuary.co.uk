<?php

declare(strict_types=1);

namespace App\Actions\Shop;

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
            $variant->decrement('quantity');

            return;
        }

        $orderItem->decrement('quantity');
        $variant->increment('quantity');

        if ($orderItem->quantity === 0) {
            $orderItem->delete();
        }
    }
}
