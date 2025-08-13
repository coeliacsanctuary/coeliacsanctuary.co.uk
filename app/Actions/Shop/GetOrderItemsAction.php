<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOrderItemsAction
{
    public function handle(ShopOrder $order): AnonymousResourceCollection
    {
        $items = $order
            ->items()
            ->with(['product', 'product.media', 'variant'])
            ->get();

        return ShopOrderItemResource::collection($items);
    }
}
