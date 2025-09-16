<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\DownloadMyProducts;

use App\Enums\Shop\ProductVariantType;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Models\Shop\ShopOrderItem;
use App\Resources\Shop\ShopDownloadMyProductsOrderItemResource;
use App\Resources\Shop\ShopDownloadMyProductsOrderResource;
use Inertia\Response;

class GetController
{
    public function __invoke(Inertia $inertia, ShopOrderDownloadLink $downloadLink): Response
    {
        /** @var ShopOrder $order */
        $order = $downloadLink->order;

        $order->loadMissing(['items', 'items.product', 'items.variant', 'items.variant.media']);

        $downloadableItems = $order
            ->items
            ->filter(fn (ShopOrderItem $item) => $item->variant?->variant_type !== ProductVariantType::PHYSICAL);

        $downloadLink->views()->create();

        return $inertia
            ->doNotTrack()
            ->metaTags([], false)
            ->title('Download my Products!')
            ->render('Shop/DownloadMyProducts/DownloadMyProducts', [
                'order' => ShopDownloadMyProductsOrderResource::make($order),
                'items' => ShopDownloadMyProductsOrderItemResource::collection($downloadableItems),
                'expires' => $downloadLink->expires_at->format('jS F Y \a\t H:i'),
            ]);
    }
}
