<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;

class ShopOrderDownloadLinkFactory extends Factory
{
    protected $model = ShopOrderDownloadLink::class;

    public function definition(): array
    {
        return [
            'order_id' => self::factoryForModel(ShopOrder::class)->asPaid()->hasDigitalProducts(),
            'expires_at' => now()->addMonth(),
        ];
    }

    public function forOrder(ShopOrder $order): self
    {
        return $this->state(fn () => [
            'order_id' => $order->id,
        ]);
    }
}
