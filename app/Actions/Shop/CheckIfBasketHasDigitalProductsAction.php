<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Database\Eloquent\Collection;

class CheckIfBasketHasDigitalProductsAction
{
    public function handle(ShopOrder $basket): void
    {
        /** @var Collection<int, ShopOrderItem> $items */
        $items = $basket->items;

        $items->loadMissing(['variant', 'product']);

        $digitalProducts = $items->filter(fn (ShopOrderItem $item) => $item->variant?->variant_type !== ProductVariantType::PHYSICAL);

        $nonDigitalProducts = $items->reject(fn (ShopOrderItem $item) => $item->variant?->variant_type === ProductVariantType::DIGITAL);

        $basket->update([
            'has_digital_products' => $digitalProducts->isNotEmpty(),
            'is_digital_only' => $nonDigitalProducts->isEmpty(),
        ]);
    }
}
