<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Shop\ShopCustomer;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSending;

class PreventEmailsSendingOnNonProductionEnvironmentsListener
{
    public function handle(NotificationSending $event): bool
    {
        $allowedEnvs = ['local', 'production'];
        $allowedEmails = config('coeliac.allowed-emails');

        if ( ! in_array(config('app.env'), $allowedEnvs)) {
            $email = $this->resolveEmailAddress($event->notifiable);

            if ( ! in_array($email, $allowedEmails)) {
                return false;
            }
        }

        return true;
    }

    protected function resolveEmailAddress(User|ShopCustomer|AnonymousNotifiable $notifiable): string
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routes['mail'];
        }

        return $notifiable->email;
    }
}
