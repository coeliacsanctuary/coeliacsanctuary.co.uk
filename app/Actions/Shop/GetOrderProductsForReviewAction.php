<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GetOrderProductsForReviewAction
{
    /** @return Collection<int, array{id: int, title: string, variants: array<int, string>, image: string|null, link: string|null, type: string}> */
    public function handle(ShopOrder $order): Collection
    {
        /** @var Collection<int, ShopOrderItem> $physicalItems */
        $physicalItems = $order->items->load([
            'product' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            'product.variants',
            'product.media',
            'variant' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
        ]);

        $digitalItems = $order->digitalItems->load([
            'card' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            'card.type',
        ]);

        return $physicalItems
            ->groupBy('product_id')
            ->concat($digitalItems->map(fn (ShopOrderDigitalItem $item) => collect([$item])))
            ->map($this->buildProduct(...))
            ->values();
    }

    /**
     * @param  Collection<int, ShopOrderItem | ShopOrderDigitalItem>  $lines
     * @return array{id: int, title: string, variants: array<int, string>, image: string|null, link: string|null, type: string}
     */
    protected function buildProduct(Collection $lines): array
    {
        $item = $lines->first();

        return match($item::class) {
            ShopOrderItem::class => $this->formatPhysicalProduct($item, $lines),
            ShopOrderDigitalItem::class => $this->formatDigitalProduct($item),
            default => throw new InvalidArgumentException('Invalid item type'),
        };
    }

    protected function formatPhysicalProduct(ShopOrderItem $item, Collection $lines): array
    {
        /** @var ShopProduct $product */
        $product = $item->product;

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
            'type' => 'physical',
        ];
    }

    protected function formatDigitalProduct(ShopOrderDigitalItem $item): array
    {
        /** @var ShopDigitalCard $card */
        $card = $item->card;

        return [
            'id' => $card->id,
            'title' => $card->title,
            'variants' => [],
            'image' => $card->type->main_image_as_webp ?? $card->type->main_image,
            'link' => $card->live ? $card->type_id->route() : null,
            'type' => 'digital',
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
