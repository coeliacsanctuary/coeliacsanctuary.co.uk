<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Collection;

class OrderResentMailable extends BaseShopMailable
{
    /** @param Collection<int, int> $overrides */
    public function __construct(protected ShopOrder $order, protected Collection $overrides, protected ?string $key = null)
    {
        parent::__construct($order, $key);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary order has been resent!')
            ->mjml('mailables.mjml.shop.order-resent', $this->baseData([
                'overrides' => $this->overrides,
                'reason' => 'to let you know your Coeliac Sanctuary order has been resent!',
            ]));
    }
}
