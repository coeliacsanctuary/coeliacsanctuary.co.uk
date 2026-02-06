<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Actions\SignUpToNewsletterAction;
use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;

class SubscribeToNewsletterIfRequired
{
    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        if ( ! $order->newsletter_signup) {
            return;
        }

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        app(SignUpToNewsletterAction::class)->handle($customer->email);
    }
}
