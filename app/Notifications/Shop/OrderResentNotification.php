<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\Shop\OrderResentMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use RuntimeException;

class OrderResentNotification extends Notification
{
    /** @param Collection<int, int> $overrides */
    public function __construct(protected ShopOrder $order, protected Collection $overrides)
    {
        throw_if($order->is_digital_only, new RuntimeException('Digital orders cannot be resent.'));
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return OrderResentMailable::make($this->order, $this->overrides, $this->key);
    }
}
