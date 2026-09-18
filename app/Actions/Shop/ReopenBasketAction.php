<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReopenBasketAction
{
    /** @return Collection<int, string> */
    public function handle(ShopOrder $basket): Collection
    {
        $basket->load([
            'items', 'items.product', 'items.variant', 'items.product.prices',
            'digitalItems', 'digitalItems.card', 'digitalItems.card.price', 'digitalItems.orderItem',
        ]);

        $basket->update(['state_id' => OrderState::BASKET]);

        /** @var Collection<int, ShopOrderItem> $items */
        $items = $basket->items;

        /** @var Collection<int, ShopOrderDigitalItem> $digitalItems */
        $digitalItems = $basket->digitalItems;

        if ($items->reject(fn (ShopOrderItem $item) => $item->variant?->quantity === 0)->isEmpty() && $digitalItems->isEmpty()) {
            return collect(['All of the items in your basket have gone out of stock']);
        }

        /** @var Collection<int, array{class-string, array}> $actions */
        $actions = new Collection;

        /** @var Collection<int, string> $warnings */
        $warnings = new Collection;

        $items->each(function (ShopOrderItem $item) use ($basket, $actions, $warnings): void {
            /** @var ShopProductVariant $variant */
            $variant = $item->variant;

            /** @var ShopProduct $product */
            $product = $item->product;

            if ($variant->quantity === 0) {
                $warnings->add($this->getWarningMessage($variant, $product));

                return;
            }

            if ($variant->quantity < $item->quantity) {
                $warnings->add($this->getWarningMessage($variant, $product));
            }

            $actions->add([AddProductToBasketAction::class, [$basket, $product, $variant, min($item->quantity, $variant->quantity)]]);
        });

        $digitalItems->each(function (ShopOrderDigitalItem $item) use ($basket, $actions, $warnings): void {
            /** @var ?ShopDigitalCard $card */
            $card = $item->card;

            if(!$card) {
                $warnings->add("Sorry, {$item->digital_card_title} is no longer available.");

                return;
            }

            $actions->add([AddDigitalCardToBasketAction::class, [$basket, $card, $item->orderItem]]);
        });

        $actions->each(function (array $callable): void {
            /** @var array{class-string, array} $callable */
            [$action, $params] = $callable;

            app($action)->handle(...$params);
        });

        return $warnings;
    }

    protected function getWarningMessage(ShopProductVariant $variant, ShopProduct $product): string
    {
        if ($variant->quantity === 0) {
            return "{$product->title}" . ($variant->title ? " in {$variant->title}" : '') . ' has gone out of stock';
        }

        $plural = Str::plural('item', $variant->quantity);
        $message = "{$product->title} only has {$variant->quantity} {$plural} left";

        if ($variant->title) {
            $message .= " in the {$variant->title} {$product->variant_title}";
        }

        return $message;
    }
}
