<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ContactFormSubmittedEvent;
use App\Events\Shop\OrderCancelledEvent;
use App\Events\Shop\OrderPaidEvent;
use App\Events\Shop\OrderShippedEvent;
use App\Listeners\PreventEmailsSendingOnNonProductionEnvironmentsListener;
use App\Listeners\SendContactFormListener;
use App\Listeners\Shop\SendOrderCancellationNotification;
use App\Listeners\Shop\SendOrderConfirmationMails;
use App\Listeners\Shop\SendOrderShippedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSending;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPaidEvent::class => [SendOrderConfirmationMails::class],
        OrderShippedEvent::class => [SendOrderShippedNotification::class],
        OrderCancelledEvent::class => [SendOrderCancellationNotification::class],
        ContactFormSubmittedEvent::class => [SendContactFormListener::class],
//        NotificationSending::class => [PreventEmailsSendingOnNonProductionEnvironmentsListener::class],
    ];
}
