<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopCategory */
class ShopCategoryIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'travelCardSearch' => $this->id === 1 || $this->id === 11,
            'products_count' => $this->whenCounted('products'),
            'price' => $this->whenLoaded('products', fn () => $this->categoryPrice()),
            'reviews_count' => $this->whenLoaded('products', fn () => $this->products->sum('reviews_count')),
        ];
    }

    protected function categoryPrice(): ?string
    {
        $cheapest = $this->products
            ->map(fn (ShopProduct $product) => $product->currentPrice)
            ->filter()
            ->min();

        if ( ! $cheapest) {
            return null;
        }

        return 'from ' . Helpers::formatMoney(Money::GBP((int) $cheapest));
    }
}
