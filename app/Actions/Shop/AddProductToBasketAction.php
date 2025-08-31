<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\ProductVariantType;
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
            'product_price' => $variant->currentPrice,
            'quantity' => $quantity,
        ]);

        if ( ! $item->wasRecentlyCreated) {
            $item->increment('quantity', $quantity);
        }

        if ($variant->variant_type !== ProductVariantType::DIGITAL) {
            $variant->decrement('quantity', $quantity);
        }

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($order);

        $order->touch();
    }
}
