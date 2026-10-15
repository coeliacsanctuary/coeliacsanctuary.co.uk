<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Enums\Shop\OrderState;
use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\MyTravelCards\MyTravelCardsInvitationNotification;

class SendMyTravelCardsInvitationIfRequired
{
    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        if ($order->has_digital_items === false) {
            return;
        }

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $customer->notify(new MyTravelCardsInvitationNotification($order));

        if ($order->is_digital_only === true) {
            $order->update(['state_id' => OrderState::SHIPPED]);
        }
    }
}
