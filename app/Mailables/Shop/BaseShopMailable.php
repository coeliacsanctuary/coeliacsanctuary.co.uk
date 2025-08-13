<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\DataObjects\NotificationRelatedObject;
use App\Mailables\BaseMailable;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use Carbon\Carbon;

abstract class BaseShopMailable extends BaseMailable
{
    protected string $emailKey = '';

    public function __construct(protected ShopOrder $order, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    protected function baseData(array $data = []): array
    {
        $this->order->loadMissing(['address', 'customer', 'items', 'items.product', 'items.variant', 'items.product.media', 'payment']);

        return array_merge([
            'date' => Carbon::now(),
            'key' => $this->emailKey,
            'order' => $this->order,
            'notifiable' => $this->order->customer,
            'relatedTitle' => 'Products',
            'relatedItems' => ShopProduct::query()
                ->take(3)
                ->inRandomOrder()
                ->get()
                ->map(fn (ShopProduct $product) => new NotificationRelatedObject(
                    title: $product->title,
                    image: $product->main_image,
                    link: $product->link,
                )),
        ], $data);
    }
}
