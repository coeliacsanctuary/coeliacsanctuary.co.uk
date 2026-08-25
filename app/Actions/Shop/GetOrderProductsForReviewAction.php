<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class GetOrderProductsForReviewAction
{
    /** @return Collection<int, array{id: int, title: string, variants: array<int, string>, image: string|null, link: string|null}> */
    public function handle(ShopOrder $order): Collection
    {
        /** @var Collection<int, ShopOrderItem> $items */
        $items = $order->items->load([
            'product' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            'product.variants',
            'product.media',
            'variant' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
        ]);

        return $items
            ->groupBy('product_id')
            ->map($this->buildProduct(...))
            ->values();
    }

    /**
     * @param  Collection<int, ShopOrderItem>  $lines
     * @return array{id: int, title: string, variants: array<int, string>, image: string|null, link: string|null}
     */
    protected function buildProduct(Collection $lines): array
    {
        /** @var ShopOrderItem $first */
        $first = $lines->first();

        /** @var ShopProduct $product */
        $product = $first->product;

        $isStillSold = $product->variants->isNotEmpty();

        return [
            'id' => $product->id,
            'title' => $product->title,
            'variants' => $lines
                ->map(fn (ShopOrderItem $item) => $item->variant?->title)
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'image' => $this->productImage($product),
            'link' => $isStillSold ? $product->link : null,
        ];
    }

    protected function productImage(ShopProduct $product): ?string
    {
        if ($product->getMedia('primary')->isEmpty()) {
            return $product->first_image;
        }

        return $product->main_image_as_webp ?? $product->main_image;
    }
}
