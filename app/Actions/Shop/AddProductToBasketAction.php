<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Database\Eloquent\Builder;

class AddProductToBasketAction
{
    public function handle(ShopOrder $order, ShopProduct $product, ShopProductVariant $variant, int $quantity, bool $includeAddOn = false): void
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

        if ($includeAddOn) {
            /** @var ShopProductAddOn $addOn */
            $addOn = $product->addOns;

            $item->update([
                'product_add_on_id' => $addOn->id,
                'product_add_on_title' => $addOn->name,
                'product_add_on_price' => $addOn->currentPrice,
            ]);
        }

        $variant->decrement('quantity', $quantity);

        $order->touch();
    }
}
