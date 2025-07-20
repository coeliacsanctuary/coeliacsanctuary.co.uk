<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;

class AddProductToBasketAction
{
    public function handle(ShopOrder $order, ShopProduct $product, ShopProductVariant $variant, int $quantity): void
    {
        $item = $order->items()->firstOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ], [
            'product_title' => $product->title,
            'product_price' => $product->currentPrice,
            'quantity' => $quantity,
        ]);

        if ( ! $item->wasRecentlyCreated) {
            $item->increment('quantity', $quantity);
        }

        $variant->decrement('quantity', $quantity);

        $order->touch();
    }
}
