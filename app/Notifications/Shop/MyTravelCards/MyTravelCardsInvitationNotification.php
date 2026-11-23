<?php

declare(strict_types=1);

namespace App\Notifications\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\Shop\MyTravelCards\MyTravelCardsInvitationMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class MyTravelCardsInvitationNotification extends Notification
{
    public function __construct(protected ShopOrder $order)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return MyTravelCardsInvitationMailable::make($this->order, $this->key);
    }
}
