<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

class RestrictNonProductionMailListener
{
    public function handle(MessageSending $event): ?bool
    {
        if ($this->canSend()) {
            return null;
        }

        $whitelist = [
            'contact@coeliacsanctuary.co.uk',
            'alisondwheatley@gmail.com',
            'jamie@jamie-peters.co.uk',
            'jamie.phpeters@gmail.com',
        ];

        $isWhitelisted = collect($event->message->getTo())
            ->every(fn (Address $address): bool => in_array($address->getAddress(), $whitelist, true));

        if ( ! $isWhitelisted) {
            return false;
        }

        $event->message->subject('[' . app()->environment() . '] ' . $event->message->getSubject());

        return null;
    }

    protected function canSend(): bool
    {
        if (app()->isProduction()) {
            return true;
        }

        if ( ! app()->isLocal()) {
            return false;
        }

        if (config()->string('mail.default') !== 'smtp') {
            return false;
        }

        return config()->string('mail.mailers.smtp.host') === '127.0.0.1';
    }
}
