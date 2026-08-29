<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Actions\Shop\GetActiveShopHolidayAction;
use App\Infrastructure\MjmlMessage;

class OrderConfirmedMailable extends BaseShopMailable
{
    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary order is confirmed!')
            ->mjml('mailables.mjml.shop.order-complete', $this->baseData([
                'reason' => 'as confirmation to an order placed in the Coeliac Sanctuary Shop.',
                'holidayNotice' => app(GetActiveShopHolidayAction::class)->handle()?->notice,
            ]));
    }
}
