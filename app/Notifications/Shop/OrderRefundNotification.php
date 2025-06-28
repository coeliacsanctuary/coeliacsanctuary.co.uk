<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\Shop\OrderCancelledMailable;
use App\Mailables\Shop\OrderRefundMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class OrderRefundNotification extends Notification
{
    public function __construct(protected ShopPaymentRefund $refund, protected string | null $refundReason)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return OrderRefundMailable::make($this->refund, $this->refundReason, $this->key);
    }
}
