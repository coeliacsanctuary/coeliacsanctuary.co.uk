<?php

declare(strict_types=1);

namespace App\Mailables\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\BaseShopMailable;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\URL;

class MyTravelCardsInvitationMailable extends BaseShopMailable
{
    protected function generateLink(): string
    {
        return URL::temporarySignedRoute(
            'my-travel-cards.login.order.get',
            CarbonInterval::week(),
            ['order' => $this->order],
        );
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your Coeliac Sanctuary digital cards are ready!')
            ->mjml('mailables.mjml.shop.my-travel-cards.invitation', $this->baseData([
                'reason' => 'so you can access your digital cards purchased through Coeliac Sanctuary Shop.',
                'downloadLink' => $this->generateLink(),
            ]));
    }
}
