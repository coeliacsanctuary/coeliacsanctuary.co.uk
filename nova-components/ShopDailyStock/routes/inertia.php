<?php

declare(strict_types=1);

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::get('/', function (NovaRequest $request) {
    $items = [];

    ShopOrder::query()
        ->whereIn('state_id', [OrderState::PAID, OrderState::READY])
        ->with(['items', 'items.product', 'items.variant', 'items.product.categories'])
        ->get()
        ->each(static function (ShopOrder $order) use (&$items): void {
            $order->items->each(static function (ShopOrderItem $item) use (&$items): void {
                if (array_key_exists($item->variant->id, $items)) {
                    $items[$item->variant->id]['quantity'] += $item->quantity;

                    return;
                }

                $items[$item->variant->id] = [
                    'quantity' => $item->quantity,
                    'item' => $item->makeVisible(['product', 'variant', 'product_title']),
                ];
            });
        });

    $return = [];

    foreach ($items as $product) {
        if ( ! array_key_exists($product['item']->product->categories[0]->title, $return)) {
            $return[$product['item']->product->categories[0]->title] = [];
        }

        $return[$product['item']->product->categories[0]->title][$product['item']->product_title] = $product['quantity'];
    }

    return inertia('ShopDailyStock', [
        'items' => $return,
    ]);
});
