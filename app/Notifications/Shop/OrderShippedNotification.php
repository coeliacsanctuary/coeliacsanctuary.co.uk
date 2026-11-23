<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\Shop\OrderShippedMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use RuntimeException;

class OrderShippedNotification extends Notification
{
    public function __construct(protected ShopOrder $order)
    {
        throw_if($order->is_digital_only, new RuntimeException('Digital orders cannot be shipped.'));
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return OrderShippedMailable::make($this->order, $this->key);
    }
}
