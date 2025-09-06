<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopOrderItem */
class ShopOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopProduct $product */
        $product = $this->product;

        /** @var ShopProductVariant $variant */
        $variant = $this->variant;

        return [
            'id' => $this->id,
            'title' => $this->product_title,
            'description' => $variant->short_description,
            'link' => $product->link,
            'variant' => $variant->title,
            'item_price' => Helpers::formatMoney(Money::GBP($this->product_price)),
            'line_price' => Helpers::formatMoney(Money::GBP($this->product_price * $this->quantity)),
            'quantity' => $this->quantity,
            'image' => $product->main_image_as_webp ?? $product->main_image,
            'variant_type' => $variant->variant_type->value,
        ];
    }
}
