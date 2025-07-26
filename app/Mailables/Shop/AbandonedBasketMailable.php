<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use Illuminate\Support\Facades\URL;

class AbandonedBasketMailable extends BaseShopMailable
{
    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary order is confirmed!')
            ->mjml('mailables.mjml.shop.static.abandoned-basket', $this->baseData([
                'link' => $this->generateMagicLink(),
                'basket' => $this->order,
                'reason' => 'as a reminder to complete your purchase',
            ]));
    }

    protected function generateMagicLink(): string
    {
        return URL::temporarySignedRoute('shop.basket.reopen', now()->addDay(), ['basket' => $this->order]);
    }
}
