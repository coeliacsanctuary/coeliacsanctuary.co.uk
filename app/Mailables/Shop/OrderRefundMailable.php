<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopPaymentRefund;

class OrderRefundMailable extends BaseShopMailable
{
    public function __construct(protected ShopPaymentRefund $refund, protected string | null $refundReason, protected string $key)
    {
        parent::__construct($refund->order, $key);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary order has received a refund')
            ->mjml('mailables.mjml.shop.order-refund', $this->baseData([
                'refund' => $this->refund,
                'refundReason' => $this->refundReason,
                'reason' => 'to let you know your Coeliac Sanctuary order has been cancelled.',
            ]));
    }
}
