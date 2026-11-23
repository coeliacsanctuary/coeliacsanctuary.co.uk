<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Actions\SignUpToNewsletterAction;
use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscribeToNewsletterIfRequired implements ShouldQueue
{
    public function handle(OrderPaidEvent $event): void
    {
        $order = $event->order;

        if ( ! $order->newsletter_signup) {
            return;
        }

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        app(SignUpToNewsletterAction::class)->handle($customer->email);
    }
}
