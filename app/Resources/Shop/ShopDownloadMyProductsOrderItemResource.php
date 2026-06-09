<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrderItem */
class ShopDownloadMyProductsOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopProduct $product */
        $product = $this->product;

        /** @var ShopProductVariant $variant */
        $variant = $this->variant;

        /** @var ShopProductAddOn $addOn */
        $addOn = $this->addOn;

        return [
            'id' => $this->id,
            'title' => $product->title,
            'image' => $product->main_image_as_webp ?? $product->main_image,
            'variant_title' => $variant->title,
            'variant_description' => $variant->short_description,
            'add_on_name' => $addOn->name,
            'add_on_description' => $addOn->description,
            'download_link' => $addOn->getMedia('download')->first()?->getTemporaryUrl(now()->addMinutes(5), options: [
                'ResponseContentDisposition' => 'attachment',
            ]),
        ];
    }
}
