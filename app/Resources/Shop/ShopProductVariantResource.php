<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Shop\ShopProductVariant */
class ShopProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->variantName(),
            'description' => $this->short_description,
            'quantity' => $this->variant_type === ProductVariantType::DIGITAL ? 999 : $this->quantity,
            'icon' => $this->icon !== [] ? $this->icon : null,
            'prices' => $this->price,
            'primary_variant' => $this->primary_variant,
            'variant_type' => $this->variant_type->value,
        ];
    }

    protected function variantName(): string
    {
        if ($this->title !== '') {
            return $this->title;
        }

        /** @var ShopProduct $product */
        $product = $this->product;

        if ($product->variants->count() > 1) {
            return $this->variant_type->label();
        }

        return '';
    }
}
